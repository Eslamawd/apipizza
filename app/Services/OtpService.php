<?php

namespace App\Services;

use Twilio\Rest\Client;

class OtpService
{
    protected $twilio;
    protected $verifySid;

    public function __construct()
    {
        $this->twilio = new Client(
            config('services.twilio.sid'),
            config('services.twilio.token')
        );

        $this->verifySid = config('services.twilio.verify_sid');
    }

    /**
     * إرسال كود التحقق
     */
    public function send(string $phone): void
    {
        try {
            $this->twilio->verify->v2->services($this->verifySid)
                ->verifications
                ->create($phone, "sms");
        } catch (\Exception $e) {
            // التعامل مع الخطأ (مثل تسجيله أو إعادة المحاولة)
            throw new \RuntimeException("Failed to send OTP: " . $e->getMessage());
        }
    }

    /**
     * إعادة إرسال الكود
     */
    public function resend(string $phone): void
    {
        $this->send($phone);
    }

    /**
     * التحقق من الكود
     */
    public function verify(string $phone, string $code): bool
    {
        $verification = $this->twilio->verify->v2->services($this->verifySid)
            ->verificationChecks
            ->create([
                'to'   => $phone,
                'code' => $code
            ]);

        return $verification->status === 'approved';
    }
}
