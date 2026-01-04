<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Jobs\SendMailAndOtp;

use App\Models\User;
use App\Services\OtpService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class AuthController extends Controller
{
    //

      protected $otpService;

    public function __construct(OtpService $otpService)
    {
        $this->otpService = $otpService;
    }

        public function register(RegisterRequest $request)
{

 

      $user = User::create([
        'name'     => $request->name,
        'email'    => $request->email,
        'phone'    => $request->phone,
        'password' => Hash::make($request->password),
    ]);

 
    // تعيين الدور حسب الإيميل
    
          if ($user->email === 'eeslamawood@gmail.com') {
               Role::firstOrCreate(['name' => 'admin']);
              $user->assignRole('admin');
            } 
            else {
                Role::firstOrCreate(['name' => 'user']);
                $user->assignRole('user');
            }
  
         

            SendMailAndOtp::dispatch($user);
            $user->affiliate()->create([]);

            auth()->login($user);
            $request->session()->regenerate(); 


    return  response()->json(['user' => new UserResource($user)]);
}

   public function login(LoginRequest $request)
{
    $request->validated();

    $user = User::where('email', $request->email)->first();

    if (! $user || ! Hash::check($request->password, $user->password)) {
        throw ValidationException::withMessages(['email' => ['Invalid credentials']]);
    }
     

    auth()->login($user);
    $request->session()->regenerate(); // حماية من جلسات مزورة

    return response()->json(['user' => new UserResource($user)]);
}


public function logout(Request $request)
{
    Auth::guard('web')->logout(); // ← هذا يعمل إذا الحارس هو web
    $request->session()->invalidate();
    $request->session()->regenerateToken();

    return response()->json(['message' => 'Logged out successfully']);
}


    public function user(Request $request)
{
    return response()->json(['user' => new UserResource($request->user())]);
}

}
