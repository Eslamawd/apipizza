<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Http\Requests\StoreOrderRequest;
use App\Services\OrderService;
use App\Services\OrderNotificationService;
use App\Services\WebSocketService;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    protected $webSocket;
    protected $orderService;
    protected $notificationService;

    public function __construct(
        WebSocketService $webSocket,
        OrderService $orderService,
        OrderNotificationService $notificationService
    ) {
        $this->webSocket = $webSocket;
        $this->orderService = $orderService;
        $this->notificationService = $notificationService;
    }
    public function index()
    {
        // كل الطلبات الجديدة أو الجارية
        $user = auth()->user();
        $orders = $user->orders()->with([ 'orderItems.item', 'orderItems.options'])->latest()->paginate(4);
        return response()->json($orders);
    }

    public function show(Order $order) {
    return response()->json($order);
    }

    public function getByKitchen(Request $request)
    {
        $restaurant = $request->get('restaurant');

        $orders = Order::with(['table', 'orderItems.item', 'orderItems.options'])
            ->where('restaurant_id', $restaurant->id)
            ->whereIn('status', ['pending', 'in_progress'])
            ->latest()
            ->get();

            if ($orders->isEmpty()) {
                return response()->json(['message' => 'No orders found for this kitchen.'], 404);
            }

        return response()->json($orders);
    }
    public function getByCashier(Request $request)
    {
        $restaurant = $request->get('restaurant');

        $orders = Order::with(['table', 'orderItems.item', 'orderItems.options'])
            ->where('restaurant_id', $restaurant->id)
            ->whereIn('status', ['delivered', 'ready','pending','in_progress'])
            ->latest()
            ->get();

            if ($orders->isEmpty()) {
                return response()->json(['message' => 'No orders found for this cashier.'], 404);
            }

        return response()->json($orders);
    }
    /**
     * إنشاء أوردر جديد
     */
    public function store(StoreOrderRequest $request)
    {
        try {
            // 1. إنشاء الأوردر
            $order = $this->orderService->createOrderFromRequest($request->validated());

            // 2. معالجة items والأسعار والدفع
            $result = $this->orderService->processOrder(
                $order,
                $request->items,
                $request->payment_token
            );

            // إذا فشل الدفع
            if (!$result['success']) {
                return response()->json(
                    $this->orderService->formatErrorResponse($result['payment_result']),
                    402
                );
            }

            // 3. إرسال التنبيهات
            $this->notificationService->notifyNewOrder($result['order']);

            // 4. الرد على العميل
            return response()->json(
                $this->orderService->formatResponse(
                    $result['order'],
                    $result['payment_result']
                ),
                201
            );
        } catch (\Exception $e) {
            \Log::error('Order creation failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'message' => 'Failed to create order. Please try again.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    public function updateStatus(Request $request, $id)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,in_progress,ready,delivered,payid,cancelled',
        ]);
        $order = Order::findOrFail($id);
        $order->update(['status' => $validated['status']]);

        // إرسال تحديث الحالة بشكل مباشر عبر service
        $this->webSocket->sendOrderUpdated(
            $order->id,
            $order->restaurant_id,
            $order->status
        );

        return response()->json($order);
    }
}
