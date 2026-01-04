<?php 

use App\Http\Controllers\InventoryItemController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// مسارات المخزون تحت حماية الـ Sanctum
Route::middleware(['auth:sanctum'])->group(function () {
    // مسار رفع ومعالجة ملف الإكسل (معالجة غير متزامنة)
    Route::post('/inventory-items/upload', [InventoryItemController::class, 'uploadAndProcess']);
    
    // مسار الإدخال اليدوي لصنف واحد
    Route::post('/inventory-items/manual', [InventoryItemController::class, 'createManualItem']);
    
    // مسارات CRUD الأساسية (للاطلاع والتعديل والحذف)
    Route::get('/inventory-items/restaurant/{restaurant}', [InventoryItemController::class, 'getByRestaurant']);
    // ملاحظة: تم حذف مساري 'store' و 'update' و 'delete' من هذا المثال لتبسيط المسارات،
    // لكن يمكنك استخدامهم إذا كنت تخطط لـ CRUD يدوي مختلف عن الـ manual-item.
    // للحفاظ على ما أرسلته، سأعيد تفعيله مع تغيير 'store' ليتناسب مع 'manual':

    // مسارات الـ CRUD الأخرى - (هذه المسارات تحتاج إلى دوال 'show', 'update', 'destroy' في الكنترولر)
    // Route::put('/inventory-items/{inventoryItem}', [InventoryItemController::class, 'update']);
    // Route::delete('/inventory-items/{inventoryItem}', [InventoryItemController::class, 'destroy']);
});