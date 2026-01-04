<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\OtpService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class VerificationController extends Controller
{
    protected $otpService;

    public function __construct(OtpService $otpService)
    {
        $this->otpService = $otpService;
    }

    public function send(Request $request)
    {
        $user = Auth::user();

        if ($user->phone_verified_at) {
            return response()->json(['message' => 'Phone already verified'], 400);
        }

        $this->otpService->send($user->phone);

        return response()->json(['message' => 'Verification code sent']);
    }

    public function resend(Request $request)
    {
        $user = Auth::user();

        if ($user->phone_verified_at) {
            return response()->json(['message' => 'Phone already verified'], 400);
        }

        $this->otpService->resend($user->phone);

        return response()->json(['message' => 'Verification code resent']);
    }

    public function verify(Request $request)
    {
        $request->validate([
            'code' => 'required|string',
        ]);

        $user = Auth::user();

        $isValid = $this->otpService->verify($user->phone, $request->code);

        if (!$isValid) {
            return response()->json(['message' => 'Invalid verification code'], 400);
        }

        $user->phone_verified_at = now();
        $user->save();

        return response()->json(['message' => 'Phone verified successfully']);
    }
}
