<?php

namespace App\Jobs;

use App\Imports\GenericImport;
use App\Services\InventoryService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;

class ProcessExcelFile implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $filePath;
    protected $nameColName;
    protected $quantityColName;
    protected $totalPriceColName;
    protected $unitColName;
    protected $receivedAtColName;
    protected $restaurantId;

    public function __construct(
        string $filePath,
        string $nameColName,
        string $quantityColName,
        string $totalPriceColName,
        string $unitColName,
        string $receivedAtColName,
        int $restaurantId
    ) {
        $this->filePath = $filePath;
        $this->nameColName = $nameColName;
        $this->quantityColName = $quantityColName;
        $this->totalPriceColName = $totalPriceColName;
        $this->unitColName = $unitColName;
        $this->receivedAtColName = $receivedAtColName;
        $this->restaurantId = $restaurantId;
    }

    public function handle(InventoryService $inventoryService)
    {
        $path = Storage::path($this->filePath);
        $totalRowsProcessed = 0;
        $errors = [];

        try {
            // تحميل البيانات من الملف
            $sheets = Excel::toCollection(new GenericImport(), $path);

            foreach ($sheets as $sheet) {

                if ($sheet->isEmpty()) {
                    continue;
                }

                // استخراج header (أول صف)
                $header = $sheet[0]->toArray();

                // البحث عن index لكل عمود حسب اسم المستخدم
                $nameIndex       = array_search($this->nameColName, $header);
                $quantityIndex   = array_search($this->quantityColName, $header);
                $priceIndex      = array_search($this->totalPriceColName, $header);
                $unitIndex       = array_search($this->unitColName, $header);
                $receivedAtIndex = array_search($this->receivedAtColName, $header);

                // التحقق أن الأعمدة موجودة بالفعل
                if (in_array(false, [$nameIndex, $quantityIndex, $priceIndex, $unitIndex, $receivedAtIndex], true)) {
                    throw new \Exception("اسم عمود غير صحيح. الرجاء التأكد من أسماء الأعمدة في الملف.");
                }

                // قراءة كل صف بعد الصف الأول
                foreach ($sheet->slice(1) as $row) {
                    $totalRowsProcessed++;

                    try {
                        $row = $row->toArray();
                        $itemData = [
                            'restaurant_id' => $this->restaurantId,
                            'name'          => $row[$nameIndex] ?? null,
                            'unit'          => strtolower($row[$unitIndex] ?? 'unit'),
                            'quantity'      => (float) ($row[$quantityIndex] ?? 0),
                            'total_price'   => (float) ($row[$priceIndex] ?? 0),
                            'received_at'   => Carbon::parse($row[$receivedAtIndex] ?? now())->format('Y-m-d'),
                            'expires_at'    => null,
                        ];

                        // التحقق من البيانات
                        if (empty($itemData['name']) || $itemData['quantity'] <= 0 || $itemData['total_price'] <= 0) {
                            throw new \Exception("بيانات غير صالحة في الصف");
                        }

                        // حفظ الصنف
                        $inventoryService->createItem($itemData);

                    } catch (\Exception $e) {
                        $errors[] = "خطأ في الصف {$totalRowsProcessed}: " . $e->getMessage();
                        Log::warning("Inventory Row Error #{$totalRowsProcessed}: " . $e->getMessage());
                    }
                }
            }

        } catch (\Exception $e) {
            Log::error('Inventory File Processing Failed: ' . $e->getMessage());

        } finally {
            // حذف الملف بعد الانتهاء
            Storage::delete($this->filePath);

            if (!empty($errors)) {
                Log::warning("Inventory Job finished with " . count($errors) . " errors.");
            } else {
                Log::info("Inventory Job Completed Successfully. Rows processed: " . $totalRowsProcessed);
            }
        }
    }
}
