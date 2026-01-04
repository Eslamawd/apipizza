<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessExcelFile; // استدعاء الـ Job
use App\Models\Restaurant;
use App\Services\InventoryService; // استدعاء الـ Service الجديد
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log; // لإضافة log في حالة الخطأ

class InventoryItemController extends Controller
{



    public function getByRestaurant(Restaurant $restaurant)
    {
        try {
            $items = $restaurant->inventoryItems()->paginate(20);

            return response()->json([
                'status' => 'success',
                'items' => $items
            ], 200);
            if(!$items->isEmpty()){
                return response()->json([
                     'status' => 'success',
                    'items' => [],
                ], 200);
            }
        } catch (\Exception $e) {
            Log::error('Get Items By Restaurant Failed: ' . $e->getMessage());
            return response()->json([
                'message' => 'فشل في جلب أصناف المخزون للمطعم المحدد.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // حقن InventoryService في الكنترولر عبر constructor أو عبر الدالة (سنستخدم الدالة هنا)

    /**
     * يستقبل ملف الإكسل/CSV وأسماء الأعمدة ويرسل المهمة إلى Job Queue.
     */
    public function uploadAndProcess(Request $request)
    {
        // 1. التحقق من صحة المدخلات (Validation)
        $request->validate([
            // السماح بـ XLSX و CSV و XLS
            'excel_file' => 'required|file|mimes:xlsx,xls,csv,txt,text/plain|max:10240', // حد أقصى 10 ميجا
            
            // التأكد من أن أسماء الأعمدة مطلوبة (كما اتفقنا)
            'name_col_name' => 'required|string|max:100',
            'quantity_col_name' => 'required|string|max:100',
            'total_price_col_name' => 'required|string|max:100',
            'unit_col_name' => 'required|string|max:100',
            'received_at_col_name' => 'required|string|max:100', // لتعيين عمود التاريخ
            'restaurant_id' => 'required|exists:restaurants,id', // التأكد من وجود المطعم
        ]);

        // 2. تخزين الملف بأمان خارج المجلد العام (Public)
        try {
            $filePath = $request->file('excel_file')->store('uploads');
        } catch (\Exception $e) {
            Log::error('File storage failed: ' . $e->getMessage());
            return response()->json([
                'message' => 'حدث خطأ أثناء تخزين الملف.',
                'error' => $e->getMessage()
            ], 500);
        }

        // 3. إرسال المهمة (Job Dispatch) إلى قائمة الانتظار
        ProcessExcelFile::dispatch(
            $filePath,
            $request->input('name_col_name'),
            $request->input('quantity_col_name'),
            $request->input('total_price_col_name'),
            $request->input('unit_col_name'),
            $request->input('received_at_col_name'),
            (int) $request->input('restaurant_id')
        );

        // 4. استجابة فورية للمستخدم
        return response()->json([
            'status' => 'success',
            'message' => 'تم استلام الملف بنجاح. جاري معالجة الأصناف وحساب التكاليف في الخلفية.',
            'job_queue' => 'inventory_processing'
        ], 202);
    }
    
    /**
     * يستقبل البيانات يدوياً ويحفظ صنف مخزون واحد.
     */
    public function createManualItem(Request $request, InventoryService $inventoryService)
    {
        // 1. التحقق من صحة المدخلات للإدخال اليدوي
        $request->validate([
            'name' => 'required|string|max:255',
            // تحديث قاعدة in: لتشمل وحدات الحجم (l, ml) والطول (m, cm)
            'unit' => 'required|in:kg,g,l,ml,m,cm,unit,box', 
            'quantity' => 'required|numeric|min:0.01',
            'total_price' => 'required|numeric|min:0.01',
            'received_at' => 'nullable|date',
            'expires_at' => 'nullable|date|after:received_at',
            'restaurant_id' => 'required|exists:restaurants,id',
        ]);
        
        // 2. تجميع البيانات للحفظ
        $data = $request->only([
            'name',
            'unit',
            'quantity',
            'total_price',
            'expires_at',
            'received_at',
            'restaurant_id'
        ]);
        
        // التأكد من تعيين تاريخ الاستلام
        $data['received_at'] = $request->input('received_at') ?? now();

        try {
            // 3. استخدام الـ Service لحساب وحفظ الصنف (باستخدام نفس المنطق)
            $item = $inventoryService->createItem($data);

            // 4. الاستجابة بالصنف المحفوظ
            return response()->json([
                'status' => 'success',
                'message' => 'تم إدخال صنف المخزون يدوياً بنجاح.',
                'item' => $item
            ], 201);
            
        } catch (\Exception $e) {
            Log::error('Manual Item Creation Failed: ' . $e->getMessage());
            return response()->json([
                'message' => 'فشل إدخال الصنف يدوياً.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}