<?php

namespace App\Http\Controllers;

use App\Models\Restaurant;
use App\Models\Table;
use App\Http\Requests\Created\TableRequest;
use App\Http\Requests\Updated\TableUpdatedRequest;
use Illuminate\Support\Facades\Storage;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class TableController extends Controller
{
    /**
     * عرض جميع الطاولات
     */
    public function index()
    {
        $tables = Table::with('restaurant')->get();
        return response()->json($tables);
    }

    /**
     * إنشاء طاولة جديدة
     */
public function store(TableRequest $request)
{
    $restaurant = Restaurant::findOrFail($request->restaurant_id);
 


    $table = Table::create([
        'restaurant_id' => $restaurant->id,
        'name' => $request->name,
    ]);
$logoRealPath = $restaurant->logo_path;



    // إنشاء QR للطاولة
    $frontend = config('app.frontend_url', 'http://localhost:3000');
    $qrData = $frontend . '/menu?table=' . $table->id
        . '&restaurant=' . $restaurant->id
        . '&user=' . $restaurant->user_id
        . '&token=' . $restaurant->token;

    $qrFileName = 'tables/qr/' . $table->id . '.png';
    $qrImage = QrCode::format('png')->merge($logoRealPath, 0.40, true) ->size(400)->style('dot')             // تحويل المربعات الداخلية لنقاط دائرية شيك
    ->eye('circle')
    ->color(255, 102, 0) // ← النسبة دي لتصغير اللوجو (25% من حجم الكود)
    ->errorCorrection('H')->generate($qrData);
    Storage::disk('public')->put($qrFileName, $qrImage);

    $table->update(['qr_code' => $qrFileName]);

    return response()->json($table);
}



    /**
     * عرض طاولة معينة
     */
    public function show(Table $table)
    {
        return response()->json($table->load('restaurant'));
    }
/**
 * تحديث بيانات الطاولة
 */
public function update(TableUpdatedRequest $request, Table $table)
{
    $restaurant = $table->restaurant;

    // تحقق من ملكية المستخدم
    if ($restaurant->user_id !== auth()->id()) {
        return response()->json(['error' => 'Unauthorized'], 403);
    }

    $data = $request->validated();
    $table->update($data);

    return response()->json(
       $table,
    );
}

/**
 * حذف الطاولة
 */
public function destroy(Table $table)
{
    $restaurant = $table->restaurant;

    // تحقق من ملكية المستخدم
    if ($restaurant->user_id !== auth()->id()) {
        return response()->json(['error' => 'Unauthorized'], 403);
    }

    // حذف QR code من التخزين
    if ($table->qr_code && Storage::disk('public')->exists($table->qr_code)) {
        Storage::disk('public')->delete($table->qr_code);
    }

    $table->delete();

    return response()->json(['message' => 'Table deleted successfully'], 200);
}

}
