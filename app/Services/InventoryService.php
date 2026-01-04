<?php

namespace App\Services;

use App\Models\InventoryItem;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class InventoryService
{
    /**
     * يحسب سعر الوحدة لجميع الأبعاد (الوزن، الحجم، الطول) بناءً على الكمية والوحدة والسعر الكلي.
     *
     * @param float $quantity الكمية الإجمالية المدمجة أو الجديدة
     * @param float $totalPrice السعر الكلي الإجمالي المدمج أو الجديد
     * @param string $unit وحدة القياس المدخلة
     * @return array يحتوي على جميع حقول أسعار الوحدة
     */
    protected function calculateUnitPrices(float $quantity, float $totalPrice, string $unit): array
    {
        $prices = [
            'unit_price_per_kg' => null,
            'unit_price_per_gram' => null,
            'unit_price_per_liter' => null,
            'unit_price_per_ml' => null,
            'unit_price_per_meter' => null,
            'unit_price_per_cm' => null,
        ];

        if ($quantity <= 0 || $totalPrice <= 0) {
            return $prices;
        }

        // سعر الوحدة الأساسي (سعر الـ 1 من الوحدة المدخلة)
        $baseUnitPrice = $totalPrice / $quantity;
        $unit = strtolower($unit);

        // منطق التحويل بناءً على الوحدة المدخلة
        switch ($unit) {
            // --- وحدات الوزن (Weight Units) ---
            case 'kg':
                $prices['unit_price_per_kg'] = $baseUnitPrice;
                $prices['unit_price_per_gram'] = $baseUnitPrice / 1000.0;
                break;
            case 'g':
                $prices['unit_price_per_kg'] = $baseUnitPrice * 1000.0;
                $prices['unit_price_per_gram'] = $baseUnitPrice;
                break;

            // --- وحدات الحجم (Volume Units) ---
            case 'l':
                $prices['unit_price_per_liter'] = $baseUnitPrice;
                $prices['unit_price_per_ml'] = $baseUnitPrice / 1000.0;
                break;
            case 'ml':
                $prices['unit_price_per_liter'] = $baseUnitPrice * 1000.0;
                $prices['unit_price_per_ml'] = $baseUnitPrice;
                break;

            // --- وحدات الطول (Length Units) ---
            case 'm':
                $prices['unit_price_per_meter'] = $baseUnitPrice;
                $prices['unit_price_per_cm'] = $baseUnitPrice / 100.0;
                break;
            case 'cm':
                $prices['unit_price_per_meter'] = $baseUnitPrice * 100.0;
                $prices['unit_price_per_cm'] = $baseUnitPrice;
                break;
                
            // --- وحدات القطعة / الصندوق (Piece/Box Units) ---
            case 'unit':
            case 'box':
            default:
                // لا توجد تحويلات إلى أبعاد أخرى
                break;
        }

        return $prices;
    }

    /**
     * إنشاء أو تحديث صنف مخزون (Upsert) مع استخدام متوسط التكلفة المرجح.
     *
     * @param array $data بيانات الصنف
     * @return InventoryItem
     * @throws \Exception
     */
    public function createItem(array $data): InventoryItem
    {
        // 1. تنسيق التواريخ
        $data['received_at'] = Carbon::parse($data['received_at'])->format('Y-m-d');
        $data['expires_at'] = isset($data['expires_at']) ? Carbon::parse($data['expires_at'])->format('Y-m-d') : null;
        
        return DB::transaction(function () use ($data) {

            // 2. ابحث عن نفس الصنف بالاسم والمطعم (مع قفل للـ Row)
            $existingItem = InventoryItem::where('name', $data['name'])
                ->where('restaurant_id', $data['restaurant_id'])
                ->lockForUpdate() // منع عمليات الكتابة المتزامنة
                ->first();
            
            // 3. لو الصنف موجود → تحديث ودمج (Weighted Average Cost)
            if ($existingItem) {
                
                // الكميات والتكاليف المدمجة
                $newQuantity    = $existingItem->quantity + $data['quantity'];
                $newTotalPrice  = $existingItem->total_price + $data['total_price'];

                // إعادة حساب الأسعار لجميع الأبعاد (باستخدام الإجمالي المدمج)
                $calculatedPrices = $this->calculateUnitPrices(
                    $newQuantity,
                    $newTotalPrice,
                    $data['unit']
                );

                // دمج حقول التحديث
                $updateData = array_merge([
                    'quantity'          => $newQuantity,
                    'total_price'       => $newTotalPrice,
                    'unit'              => $data['unit'], // نحدث الوحدة إذا تغيرت (افتراضياً يجب أن تكون ثابتة)
                    'received_at'       => $data['received_at'], // تحديث تاريخ الاستلام ليعكس آخر دفعة
                    'expires_at'        => $data['expires_at'] ?? $existingItem->expires_at,
                ], $calculatedPrices); // دمج أسعار الوحدة الجديدة

                $existingItem->update($updateData);

                return $existingItem;
            }

            // 4. لو مش موجود → إنشاء جديد
            
            // حساب الأسعار للصنف الجديد
            $calculatedPrices = $this->calculateUnitPrices(
                $data['quantity'],
                $data['total_price'],
                $data['unit']
            );
            
            // دمج البيانات المدخلة مع الأسعار المحسوبة
            $itemData = array_merge($data, $calculatedPrices);

            return InventoryItem::create($itemData);
        });
    }
}