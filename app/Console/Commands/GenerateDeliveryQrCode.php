<?php

namespace App\Console\Commands;

use App\Models\Restaurant;
use Illuminate\Console\Command;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Support\Facades\Storage;

class GenerateDeliveryQrCode extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'qrcode:delivery {--restaurant-id=* : Restaurant ID (لو ما حددت ه تولد لكل المطاعم)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'توليد QR code للـ Delivery (طلب التوصيل) - https://pizzagyropartyrestaurant.com/delivery';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $restaurantIds = $this->option('restaurant-id');
        
        if (empty($restaurantIds)) {
            // لو ما حددش ID معين - خذ كل المطاعم
            $restaurants = Restaurant::all();
            $this->info('⏳ توليد QR codes لـ ' . $restaurants->count() . ' مطعم...');
        } else {
            // لو حددش IDs معينة
            $restaurants = Restaurant::whereIn('id', $restaurantIds)->get();
            $this->info('⏳ توليد QR codes لـ ' . $restaurants->count() . ' مطاعم محددة...');
        }

        if ($restaurants->isEmpty()) {
            $this->error('❌ ما فيش مطاعم لتوليد QR codes ليها!');
            return;
        }

        foreach ($restaurants as $restaurant) {
            try {
                // الرابط الخاص بـ Delivery
                $deliveryUrl = 'https://pizzagyropartyrestaurant.com/delivry';
                
                // قراءة اللوجو إن كان موجود - استخدم getRawOriginal للحصول على القيمة الأصلية
                $logoPath = null;
                if ($restaurant->getRawOriginal('logo')) {
                    $logoPath = storage_path('app/public/' . $restaurant->getRawOriginal('logo'));
                    if (!file_exists($logoPath)) {
                        $logoPath = null;
                    }
                }

                // إنشاء QR code بنفس الأسلوب (برتقالي + dot style + circle eyes)
                $qrImage = QrCode::format('png')
                    ->merge($logoPath, 0.40, true)  // إضافة اللوجو في الوسط
                    ->size(400)
                    ->style('dot')  // النقاط الدائرية الشيك
                    ->eye('circle')  // العيون الدائرية
                    ->color(255, 102, 0)  // اللون البرتقالي
                    ->errorCorrection('H')
                    ->generate($deliveryUrl);

                // حفظ الـ QR code
                $qrFileName = 'delivery/qr/' . $restaurant->id . '.png';
                Storage::disk('public')->put($qrFileName, $qrImage);

                $this->info("✅ تم إنشاء QR code للمطعم: {$restaurant->name}");
                $this->line("   📍 الملف: storage/app/public/{$qrFileName}");
                $this->line("   🔗 الرابط: {$deliveryUrl}");

            } catch (\Exception $e) {
                $this->error("❌ خطأ في المطعم {$restaurant->name}: " . $e->getMessage());
            }
        }

        $this->newLine();
        $this->info('✨ تم إنهاء التوليد!');
    }
}
