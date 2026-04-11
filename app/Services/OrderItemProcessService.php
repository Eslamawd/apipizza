<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderItemOption;
use App\Models\Item;

class OrderItemProcessService
{
    /**
     * معالجة الـ items وإضافتها للأوردر
     */
    public function addItemsToOrder(Order $order, array $itemsData): array
    {
        $totals = [
            'subtotalBeforeDiscount' => 0,
            'discountTotal' => 0,
            'subtotalAfterDiscount' => 0,
        ];

        foreach ($itemsData as $itemData) {
            $item = Item::with(['category.menu'])->findOrFail($itemData['item_id']);
            
            // حساب سعر الـ options
            $optionsData = $this->processOptions($itemData['options'] ?? []);
            
            // حساب السعر الكلي للـ item
            $unitPrice = $item->price + $optionsData['optionsSum'];
            $lineSubtotalBeforeDiscount = $unitPrice * $itemData['quantity'];
            
            // حساب الخصم
            $discount = $this->calculateDiscount($item);
            $lineDiscountAmount = $lineSubtotalBeforeDiscount * ($discount / 100);
            $lineSubtotalAfterDiscount = $lineSubtotalBeforeDiscount - $lineDiscountAmount;
            
            // حفظ الـ order item
            $orderItem = OrderItem::create([
                'order_id' => $order->id,
                'item_id' => $item->id,
                'comment' => $itemData['comment'] ?? null,
                'quantity' => $itemData['quantity'],
                'price' => $unitPrice,
                'subtotal' => $lineSubtotalAfterDiscount,
            ]);
            
            // حفظ الـ options
            $this->saveItemOptions($orderItem, $optionsData['optionsToSave']);
            
            // تجميع الإجماليات
            $totals['subtotalBeforeDiscount'] += $lineSubtotalBeforeDiscount;
            $totals['discountTotal'] += $lineDiscountAmount;
            $totals['subtotalAfterDiscount'] += $lineSubtotalAfterDiscount;
        }

        return $totals;
    }

    /**
     * معالجة الـ options وحساب أسعارها
     */
    private function processOptions(array $optionsData): array
    {
        $optionsSum = 0;
        $optionsToSave = [];
        $currentSizeName = $this->extractSizeName($optionsData);

        foreach ($optionsData as $optionData) {
            $option = \App\Models\ItemOption::find($optionData['id']);
            if (!$option) continue;

            $priceWithExtra = $this->calculateOptionPrice(
                $option,
                $currentSizeName
            );

            $optionsSum += $priceWithExtra;
            $optionsToSave[] = [
                'item_option_id' => $option->id,
                'position' => $optionData['position'],
                'price' => $priceWithExtra,
            ];
        }

        return [
            'optionsSum' => $optionsSum,
            'optionsToSave' => $optionsToSave,
        ];
    }

    /**
     * استخراج حجم الـ pizza من الـ options
     */
    private function extractSizeName(array $optionsData): string
    {
        foreach ($optionsData as $optionData) {
            $opt = \App\Models\ItemOption::find($optionData['id']);
            if ($opt && strtolower($opt->option_type ?? '') === 'size') {
                return strtolower($opt->name);
            }
        }
        return "";
    }

    /**
     * حساب سعر الـ option مع الإضافات حسب الحجم
     */
    private function calculateOptionPrice($option, string $currentSizeName): float
    {
        $price = (float) $option->price;
        $group = strtolower($option->option_type ?? '');

        if (in_array($group, ['topping', 'extra'])) {
            if ($currentSizeName === 'm' || $currentSizeName === 'medium') {
                $price += 0.25;
            } elseif ($currentSizeName === 'l' || $currentSizeName === 'large') {
                $price += 0.50;
            } elseif (str_contains($currentSizeName, 'xl')) {
                $price += 0.75;
            }
        }

        return $price;
    }

    /**
     * حساب نسبة الخصم (من الـ category أو الـ menu)
     */
    private function calculateDiscount($item): float
    {
        $categoryDiscount = (float) ($item->category->discount_percentage ?? 0);
        $menuDiscount = (float) ($item->category->menu->discount_percentage ?? 0);
        
        return $categoryDiscount > 0 ? $categoryDiscount : $menuDiscount;
    }

    /**
     * حفظ الـ item options
     */
    private function saveItemOptions(OrderItem $orderItem, array $optionsToSave): void
    {
        foreach ($optionsToSave as $opt) {
            OrderItemOption::create([
                'order_item_id' => $orderItem->id,
                'item_option_id' => $opt['item_option_id'],
                'position' => $opt['position'],
            ]);
        }
    }
}
