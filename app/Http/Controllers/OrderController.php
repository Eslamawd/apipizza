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
    $request->validate([
        'restaurant_id' => 'required|exists:restaurants,id',
        'table_id'      => 'nullable|exists:tables,id',
        'items'         => 'required|array|min:1',
        'items.*.item_id'   => 'required|exists:items,id',
        'items.*.quantity'  => 'required|integer|min:1',
        'items.*.options'            => 'nullable|array',
        'items.*.options.*.id'       => 'required|exists:item_options,id',
        'items.*.options.*.position' => 'required|in:whole,right,left', 
    ]);

    $order = Order::create([
        'restaurant_id' => $request->restaurant_id,
        'table_id'      => $request->table_id ?: null,
        'user_id'       => auth()->id() ?: null,
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
        
        $optionsSum = 0;
        $optionsToSave = [];

        // --- خطوة هامة: تحديد الحجم المختار لهذا الصنف ---
        $currentSizeName = "";
        if (!empty($itemData['options'])) {
            foreach ($itemData['options'] as $optionData) {
                $opt = \App\Models\ItemOption::find($optionData['id']);
                // نفترض أن الأوبشن الخاص بالحجم ينتمي لجروب اسمه 'size' أو 'Size'
                // أو ابحث عنه بطريقة تناسب قاعدة بياناتك
                if ($opt && strtolower($opt->option_type ?? '') === 'size') {
                    $currentSizeName = strtolower($opt->name);
                }
            }
        }

        // 2. معالجة الإضافات وحساب سعرها مع الزيادة الديناميكية
        if (!empty($itemData['options'])) {
            foreach ($itemData['options'] as $optionData) {
                $option = \App\Models\ItemOption::find($optionData['id']);
                
                $priceWithExtra = $option->price;

                // تطبيق الزيادة فقط إذا كان الأوبشن "إضافة" (Topping/Extra)
                // تأكد من مسمى الجروب عندك في القاعدة
                $group = strtolower($option->option_type ?? '');
                if ($group === 'topping' || $group === 'extra') {
                    if ($currentSizeName === 'm' || $currentSizeName === 'medium') {
                        $priceWithExtra += 0.25;
                    } elseif ($currentSizeName === 'l' || $currentSizeName === 'large') {
                        $priceWithExtra += 0.50;
                    } elseif (str_contains($currentSizeName, 'xl')) {
                        $priceWithExtra += 0.75;
                    }
                }

                $optionsSum += $priceWithExtra;

                $optionsToSave[] = [
                    'item_option_id' => $option->id,
                    'position'       => $optionData['position']
                ];
            }
        }

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

        foreach ($optionsToSave as $opt) {
            OrderItemOption::create([
                'order_item_id'  => $orderItem->id,
                'item_option_id' => $opt['item_option_id'],
                'position'       => $opt['position'],
            ]);
        }

        $orderTotal += $subtotal;
    }
// Apply Fees and Taxes
        if ($request->filled('longitude')) {
            $orderTotal += 5; // Shipping fee
        }
        
        $tax = $orderTotal * 0.095; 
        $finalTotal = $orderTotal + $tax;

        $order->update(['total_price' => $finalTotal]);

    // إعادة تحميل البيانات كاملة للإشعار
    $data = Order::with([
        'table:id,name',
        'restaurant:id,name',
        'orderItems.item',
        'orderItems.options'
    ])->find($order->id);

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
