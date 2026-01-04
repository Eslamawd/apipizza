<?php

namespace App\Http\Controllers;

use App\Jobs\SendNewOrderNotification;
use App\Jobs\SendUpdateOrderNotification;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderItemOption;
use App\Models\Item;
use App\Services\WebSocketService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http; // لو هنبعت للـ WebSocket Server

class OrderController extends Controller
{

     protected $webSocket;

    public function __construct(WebSocketService $webSocket)
    {
        $this->webSocket = $webSocket;
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
    
   public function store(Request $request)
{
    // 1. إنشاء الطلب الأساسي (مبدئياً بسعر 0 لحد ما نحسب محتوياته)
    $request->validate([
        'restaurant_id' => 'required|exists:restaurants,id',
        'table_id'      => 'nullable|exists:tables,id',
        'phone'         => 'nullable|string|max:20',
        'address'       => 'nullable|string|max:255',
        'latitude'      => 'nullable|numeric',
        'longitude'     => 'nullable|numeric',
        'items'         => 'required|array|min:1',
        'items.*.item_id'   => 'required|exists:items,id',
        'items.*.quantity'  => 'required|integer|min:1',
        'items.*.options'   => 'nullable|array',
        'items.*.options.*' => 'exists:item_options,id',
        'items.*.comment'   => 'nullable|string|max:500',
    ]);
    $order = Order::create([
        'restaurant_id' => $request->restaurant_id,
        'table_id'      => $request->table_id ?: null, // هينزل null لو دليفري
        'user_id'       => auth()->id() ?: null,      // لو السيستم فيه تسجيل دخول
        'phone'         => $request->phone,
        'address'       => $request->address,
        'latitude'      => $request->latitude,
        'longitude'     => $request->longitude,
        'total_price'   => 0, 
        'status'        => 'pending',
    ]);

    $orderTotal = 0;

    foreach ($request->items as $itemData) {
        $item = Item::findOrFail($itemData['item_id']);
        
        // جلب أسعار الإضافات المختارة من قاعدة البيانات مباشرة للأمان
        $selectedOptions = \App\Models\ItemOption::whereIn('id', $itemData['options'] ?? [])->get();
        $optionsSum = $selectedOptions->sum('price');

        // سعر الصنف الواحد = السعر الأساسي + سعر الإضافات
        $unitPrice = $item->price + $optionsSum;
        $subtotal = $unitPrice * $itemData['quantity'];

        $orderItem = OrderItem::create([
            'order_id' => $order->id,
            'item_id'  => $item->id,
            'comment'  => $itemData['comment'] ?? null,
            'quantity' => $itemData['quantity'],
            'price'    => $unitPrice,
            'subtotal' => $subtotal,
        ]);

        // ربط الإضافات بالـ OrderItem
        foreach ($selectedOptions as $option) {
            OrderItemOption::create([
                'order_item_id'  => $orderItem->id,
                'item_option_id' => $option->id,
            ]);
        }

        $orderTotal += $subtotal;
    }

    // 2. تحديث إجمالي الطلب النهائي في الـ Order
    $order->update(['total_price' => $orderTotal]);

    // 3. تحميل البيانات كاملة للإشعار (Eager Loading)
    $data = Order::with([
        'table:id,name',
        'restaurant:id,name',
        'orderItems.item',
        'orderItems.options'
    ])->find($order->id);

    // 🔔 إشعار واحد للمطبخ/الكاشير
    SendNewOrderNotification::dispatch($data);

    return response()->json($data, 201);
}
    public function updateStatus(Request $request, $id)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,in_progress,ready,delivered,payid,cancelled',
        ]);
        $order = Order::findOrFail($id);
        $order->update(['status' => $validated['status']]);

        // إرسال تحديث الحالة للمطبخ أو الكاشير
SendUpdateOrderNotification::dispatch(
            $order->id, 
            $order->restaurant_id, 
            $order->status // 👈 المتغير المفقود
        );

        return response()->json($order);
    }



}
