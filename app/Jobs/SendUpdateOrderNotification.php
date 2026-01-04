<?php

namespace App\Jobs;

use App\Services\WebSocketService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendUpdateOrderNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $orderId;
    public $restaurantId;
    public $status;

    /**
     * إنشاء مثيل جديد للمهمة.
     *
     * @param int $orderId
     * @param int $restaurantId
     * @param string $status
     * @return void
     */
    public function __construct(int $orderId, int $restaurantId, string $status)
    {
        $this->orderId = $orderId;
        $this->restaurantId = $restaurantId;
        $this->status = $status;
    }

    /**
     * تنفيذ المهمة.
     *
     * @param \App\Services\WebSocketService $webSocketService (حقن الخدمة تلقائيًا)
     * @return void
     */
    public function handle(WebSocketService $webSocketService): void
    {
        // 🔔 إرسال تحديث الحالة عبر الـ Job في الخلفية
        $webSocketService->sendOrderUpdated($this->orderId, $this->restaurantId, $this->status);
    }
}
