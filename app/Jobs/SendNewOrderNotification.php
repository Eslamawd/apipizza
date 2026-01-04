<?php

namespace App\Jobs;

use App\Models\Order;
use App\Services\WebSocketService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendNewOrderNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * الطلب الذي سيتم إرسال إشعاره.
     * @var \App\Models\Order
     */
    protected $order;

    /**
     * ✅ هذا السطر يخبر Laravel بتحميل العلاقات المطلوبة عند تنفيذ الـ Job.
     * @var array
     */
  


    /**
     * إنشاء مثيل جديد للمهمة.
     *
     * @param \App\Models\Order $order
     * @return void
     */
    public function __construct( $order)
    {
        // هنا يتم تمرير النموذج الأساسي (غالباً يحوي فقط ID)
        $this->order = $order; 
    }

    /**
     * تنفيذ المهمة.
     *
     * @param \App\Services\WebSocketService $webSocketService (حقن الخدمة تلقائيًا)
     * @return void
     */
    public function handle(WebSocketService $webSocketService)
    {
        // 🔔 عند التنفيذ هنا، سيكون $this->order مُحملاً بالعلاقات المذكورة في $with
        $webSocketService->sendNewOrder($this->order);
    }
}
