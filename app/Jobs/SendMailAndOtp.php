<?php

namespace App\Jobs;


use App\Models\User;
use App\Services\OtpService;
use Illuminate\Auth\Events\Registered;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Bus\Queueable;

class SendMailAndOtp implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $user;
    
    /**
     * Create a new job instance.
     */
    public function __construct(User $user)
    {
        // نخزن اليوزر بس (Laravel بيعمل serialize للـ model ID)
        $this->user = $user;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // تشغيل Event الإيميل (verification)
        event(new Registered($this->user));

        // إرسال OTP


    }
}
