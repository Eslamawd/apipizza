<?php 

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\WalletController;
use App\Models\User;
use Illuminate\Http\Request; 
use Illuminate\Support\Facades\Route;


Route::middleware(['auth:sanctum'])->group(function () {

      Route::post('/logout', [AuthController::class, 'logout']);
      Route::get('/user', [AuthController::class, 'user']);
      Route::get('/wallet/balance', [WalletController::class, 'balance']);

          Route::get('/email/verify', function () {
        return response()->json([
            'message' => 'Your email address is not verified.'
        ], 403);
    })->name('verification.notice');

     // إعادة إرسال رابط التحقق
    Route::post('/email/verification-notification', function (Request $request) {
        if ($request->user()->hasVerifiedEmail()) {
            return response()->json(['message' => 'Already verified']);
        }

        $request->user()->sendEmailVerificationNotification();

        return response()->json(['message' => 'Verification link sent!']);
    })->name('verification.send');

});


Route::get('/email/verify/{id}/{hash}', function (Request $request, $id, $hash) {
    $user = User::find($id);

    if (! $user) {
        return response()->json(['message' => 'User not found.'], 404);
    }

    // تحقق من صحة الـ hash
    if (! hash_equals((string) $hash, sha1($user->getEmailForVerification()))) {
        return response()->json(['message' => 'Invalid verification link.'], 403);
    }

    if ($user->hasVerifiedEmail()) {
        return response()->json(['message' => 'Email already verified.'], 200);
    }

    $user->markEmailAsVerified();

    return response()->json(['message' => 'Email verified successfully.'], 200);
})->name('verification.verify');



Route::post('/forgot-password', [ForgotPasswordController::class, 'sendResetLinkEmail']);
Route::post('/reset-password', [ResetPasswordController::class, 'reset']);
