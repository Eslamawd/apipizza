<?php

namespace App\Http\Controllers;

use App\Models\Restaurant;
use App\Http\Requests\Created\RestaurantRequest;
use App\Http\Requests\Updated\RestaurantUpdatedRequest;
use App\Models\User;
use Illuminate\Auth\Events\Validated;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class RestaurantController extends Controller
{
    /**
     * عرض كل المطاعم
     */
    public function index()
    {
        $restaurants = Restaurant::with(['tables', 'menus'])->get();
        return response()->json($restaurants);
       
    }
    public function getByAdmin(Request $request)
    {
       // ✅ التحقق من صحة البيانات
    $validated = $request->validate([
        'user' => 'required|integer|exists:users,id',
    ]);

    // ✅ جلب المستخدم
    $user = User::findOrFail($validated['user']);

    // ✅ جلب المطاعم مع الجداول والمنيوهات
    $restaurants = $user->restaurants()
        ->with(['tables', 'menus'])
        ->get();

         return response()->json($restaurants);
    }

    /**
     * getOrderedRestaurants 
     */
   public function getOrdersRestaurant(Restaurant $restaurant, Request $request)
{
    $validated = $request->validate([
        'state' => 'nullable|in:payid,completed,pending,in_progress,ready,delivered,cancelled',
    ]);

    $ordersQuery = $restaurant->orders()
        ->when($validated['state'] ?? null, function ($query, $state) {
            $query->where('status', $state);
        });

    $orders = $ordersQuery->latest('id')->with(['table', 'orderItems.item', 'orderItems.options'])->paginate(10);
    $revenue = $restaurant->orders()->where('status', 'payid')->sum('total_price');
    $countOrders = $ordersQuery->count();

    return response()->json([
        'orders'  => $orders,
        'revenue' => $revenue,
        'count'   => $countOrders,
    ]);
}

    /**
     * إنشاء مطعم جديد
     */
    public function store(RestaurantRequest $request)
    {
        // حفظ الصور إن وُجدت
        $logoPath = $request->hasFile('logo')
            ? $request->file('logo')->store('restaurants/logos', 'public')
            : null;

        $coverPath = $request->hasFile('cover')
            ? $request->file('cover')->store('restaurants/covers', 'public')
            : null;

            $user_id = auth()->id();
        // إنشاء المطعم
        $restaurant = Restaurant::create([
            'user_id' => $user_id,
            'name' => $request->name,
            'type' => $request->type,
            'phone' => $request->phone,
            'address' => $request->address,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'logo' => $logoPath,
            'cover' => $coverPath,
        ]);

        $logoFullPath = $logoPath ? storage_path('app/public/' . $logoPath) : null;


            /**
     * ✅ إنشاء Kitchen و Cashier مربوطين بالمطعم الجديد
     * - التوكن بيتولد أوتوماتيك من داخل الموديل نفسه
     * - الـ QR هنعمله بعدين لكل واحد فيهم
     */
    $kitchen = $restaurant->kitchens()->create([]);
    $cashier = $restaurant->cashiers()->create([]);


        // إنشاء كود QR للمطعم
        
     $frontend = config('app.frontend_url', 'http://localhost:3000');
       $qrData = $frontend . '/menu?restaurant=' . $restaurant->id . '&user=' . $user_id . '&token=' . $restaurant->token;
       // أو أي رابط خاص بالمطعم
        $qrFileName = 'restaurants/qr/' . $restaurant->id . '.png';
        $qrImage = 
        QrCode::format('png')->merge($logoFullPath, 0.40, true) ->size(400)->style('dot')             // تحويل المربعات الداخلية لنقاط دائرية شيك
    ->eye('circle')
    ->color(255, 102, 0) // ← النسبة دي لتصغير اللوجو (25% من حجم الكود)
   ->errorCorrection('H')->generate($qrData);
        Storage::disk('public')->put($qrFileName, $qrImage);

        $restaurant->update(['qr_code' => $qrFileName]);

            /**
     * ✅ توليد QR لكل من Kitchen و Cashier
     */
    $kitchenQr = $frontend . '/kitchen/' . $kitchen->id . '?restaurant=' . $restaurant->id .'&user=' . $user_id . '&token=' . $kitchen->token;

   $cashierQr = $frontend . '/cashier/' . $cashier->id . '?restaurant=' . $restaurant->id . '&user=' . $user_id . '&token=' . $cashier->token;

    
    $kitchenQrPath = 'kitchens/qr/' . $kitchen->id . '.png';
    $cashierQrPath = 'cashiers/qr/' . $cashier->id . '.png';

    Storage::disk('public')->put($kitchenQrPath, QrCode::format('png')->merge($logoFullPath, 0.40, true)
    ->style('dot')             // تحويل المربعات الداخلية لنقاط دائرية شيك
    ->eye('circle')
    ->color(255, 102, 0) // ← النسبة دي لتصغير اللوجو (25% من حجم الكود)
    ->size(400) 
    ->errorCorrection('H')->generate($kitchenQr));
    Storage::disk('public')->put($cashierQrPath, QrCode::format('png')->merge($logoFullPath, 0.40, true)->style('dot')             // تحويل المربعات الداخلية لنقاط دائرية شيك
    ->eye('circle')
    ->color(255, 102, 0) // ← النسبة دي لتصغير اللوجو (25% من حجم الكود)
    ->size(400)
    ->errorCorrection('H')->generate($cashierQr));


    $kitchen->update(['qr_code' => $kitchenQrPath]);
    $cashier->update(['qr_code' => $cashierQrPath]);

        return response()->json($restaurant);
    }

    /**
     * عرض مطعم واحد بالتفاصيل
     */
    public function show(Restaurant $restaurant)
    {
        $restaurant->load(['tables','cashiers','kitchens', 'menus.categories', 'links']);
        return response()->json($restaurant);
    }
    public function getByUser(Restaurant $restaurant)
    {
        $restaurant->load([ 'menus.categories.items.options','links']);
        return response()->json($restaurant);
    }

    /**
     * تحديث بيانات مطعم
     */
    public function update(RestaurantUpdatedRequest $request, Restaurant $restaurant)
    {
        // لو المستخدم فقط هو المالك
        

        $data = $request->validated();

        // تحديث الصور إن وُجدت
        if ($request->hasFile('logo')) {
            if ($restaurant->logo && Storage::disk('public')->exists($restaurant->logo)) {
                Storage::disk('public')->delete($restaurant->logo);
            }
            $data['logo'] = $request->file('logo')->store('restaurants/logos', 'public');
        }

        if ($request->hasFile('cover')) {
            if ($restaurant->cover && Storage::disk('public')->exists($restaurant->cover)) {
                Storage::disk('public')->delete($restaurant->cover);
            }
            $data['cover'] = $request->file('cover')->store('restaurants/covers', 'public');
        }

        $restaurant->update($data);

        return response()->json([
            'message' => 'Restaurant updated successfully',
            'data' => $restaurant,
        ]);
    }

    /**
     * حذف مطعم
     */
    public function destroy(Restaurant $restaurant)
    {
        if (auth()->id() !== $restaurant->user_id || !auth()->user()->hasRole    ('admin')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // حذف الصور والـQR
        foreach (['logo', 'cover', 'qr_code'] as $file) {
            if ($restaurant->$file && Storage::disk('public')->exists($restaurant->$file)) {
                Storage::disk('public')->delete($restaurant->$file);
            }
        }

        $restaurant->delete();

        return response()->json(['message' => 'Restaurant deleted successfully'], 204);
    }
public function getResOrdRevCount()
{
    $user = auth()->user();

    // ✅ تحقق من وجود مطاعم للمستخدم
    if (Restaurant::all()->count() === 0) {
        return response()->json([
            'restaurants_count' => 0,
            'orders_count' => 0,
            'total_revenue' => 0,
            'orders_by_restaurant' => [],
            'monthly_revenue' => [],
            'top_restaurants' => [],
            'message' => 'No data yet, add your first restaurant to see stats.'
        ]);
    }

    // ✅ جميع مطاعم المستخدم
    $restaurants = Restaurant::all();

    // ✅ عدد المطاعم
    $restaurantsCount = $restaurants->count();

    // ✅ IDs لكل المطاعم
    $restaurantIds = $restaurants->pluck('id');

    // ✅ الطلبات الخاصة بكل المطاعم
    $orders = \App\Models\Order::whereIn('restaurant_id', $restaurantIds);

    // ✅ عدد الطلبات
    $ordersCount = $orders->count();

    // ✅ إجمالي الإيرادات
    $totalRevenue = $orders->sum('total_price');

    // ✅ (1) الطلبات لكل مطعم (Bar chart)
    $ordersByRestaurant = $restaurants->map(function ($restaurant) {
        $ordersCount = $restaurant->orders()->count();
        return [
            'name' => $restaurant->name,
            'orders' => $ordersCount,
        ];
    });

    // ✅ (2) الإيرادات الشهرية (Line chart)
    $monthlyRevenue = \App\Models\Order::selectRaw('MONTH(created_at) as month, SUM(total_price) as revenue')
        ->whereIn('restaurant_id', $restaurantIds)
        ->groupBy('month')
        ->orderBy('month')
        ->get()
        ->map(function ($item) {
            return [
                'month' => date("M", mktime(0, 0, 0, $item->month, 1)), // مثل Jan, Feb
                'revenue' => (float) $item->revenue,
            ];
        });

    // ✅ (3) أعلى 5 مطاعم حسب الإيرادات (Pie chart)
    $topRestaurants = $restaurants->map(function ($restaurant) {
        $total = $restaurant->orders()->sum('total_price');
        return [
            'name' => $restaurant->name,
            'revenue' => (float) $total,
        ];
    })
    ->sortByDesc('revenue')
    ->take(5)
    ->values();

    // ✅ رجّع كل البيانات
    return response()->json([
        'restaurants_count' => $restaurantsCount,
        'orders_count' => $ordersCount,
        'total_revenue' => $totalRevenue,
        'orders_by_restaurant' => $ordersByRestaurant,
        'monthly_revenue' => $monthlyRevenue,
        'top_restaurants' => $topRestaurants,
    ]);
}

}
