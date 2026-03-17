<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Jobs\SendMailAndOtp;
use App\Models\Restaurant;

use App\Models\User;
use App\Services\OtpService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Laravel\Sanctum\PersonalAccessToken;
use Spatie\Permission\Models\Role;

class AuthController extends Controller
{
    //

      protected $otpService;

    public function __construct(OtpService $otpService)
    {
        $this->otpService = $otpService;
    }

    protected function createUserFromRequest(RegisterRequest $request): User
    {
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => Hash::make($request->password),
        ]);

        if ($user->email === 'eeslamawood@gmail.com') {
            Role::firstOrCreate(['name' => 'admin']);
            $user->assignRole('admin');
        } else {
            Role::firstOrCreate(['name' => 'user']);
            $user->assignRole('user');
        }

        SendMailAndOtp::dispatch($user);
        $user->affiliate()->create([]);

        return $user;
    }

    protected function issueMobileToken(User $user, ?string $deviceName = null): string
    {
        $tokenName = $deviceName ?: request()->userAgent() ?: 'mobile-device';

        return $user->createToken($tokenName)->plainTextToken;
    }

        public function register(RegisterRequest $request)
{

      $user = $this->createUserFromRequest($request);

    Auth::guard('web')->login($user);
      $request->session()->regenerate(); 


    return  response()->json(['user' => new UserResource($user)]);
}

public function registerMobile(RegisterRequest $request)
{
    $user = $this->createUserFromRequest($request);
    $token = $this->issueMobileToken($user, $request->input('device_name'));

    return response()->json([
        'token' => $token,
        'user' => new UserResource($user),
    ], 201);
}

   public function login(LoginRequest $request)
{
    $request->validated();

    $user = User::where('email', $request->email)->first();

    if (! $user || ! Hash::check($request->password, $user->password)) {
        throw ValidationException::withMessages(['email' => ['Invalid credentials']]);
    }
     

    Auth::guard('web')->login($user);
    $request->session()->regenerate(); // حماية من جلسات مزورة

    return response()->json(['user' => new UserResource($user)]);
}

public function loginMobile(LoginRequest $request)
{
    $request->validated();

    $user = User::where('email', $request->email)->first();

    if (! $user || ! Hash::check($request->password, $user->password)) {
        throw ValidationException::withMessages(['email' => ['Invalid credentials']]);
    }

    $token = $this->issueMobileToken($user, $request->input('device_name'));

    return response()->json([
        'token' => $token,
        'user' => new UserResource($user),
    ]);
}


public function logout(Request $request)
{
    if ($request->user()?->currentAccessToken()) {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logged out successfully']);
    }

    $bearerToken = $request->bearerToken();

    if ($bearerToken) {
        $accessToken = PersonalAccessToken::findToken($bearerToken);

        if ($accessToken) {
            $accessToken->delete();

            return response()->json(['message' => 'Logged out successfully']);
        }
    }

    Auth::guard('web')->logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();

    return response()->json(['message' => 'Logged out successfully']);
}

public function cashierContext(Request $request)
{
    $user = $request->user();

    if (! $user || ! ($user->hasRole('cashier') || $user->hasRole('admin'))) {
        return response()->json(['message' => 'Forbidden. Cashier or admin role required.'], 403);
    }

    $validated = $request->validate([
        'restaurant_id' => 'nullable|integer|exists:restaurants,id',
    ]);

    $selectedRestaurantId = $validated['restaurant_id'] ?? null;

    $restaurantQuery = Restaurant::with('cashiers');

    if ($user->hasRole('admin')) {
        if ($selectedRestaurantId) {
            $restaurantQuery->where('id', $selectedRestaurantId);
        }
    } else {
        $restaurantQuery->where('user_id', $user->id);
    }

    $restaurant = $restaurantQuery->first();

    if (! $restaurant) {
        return response()->json(['message' => 'No restaurant found for this cashier context.'], 404);
    }

    $cashier = $restaurant->cashiers->first();

    if (! $cashier) {
        return response()->json(['message' => 'No cashier profile found for this account.'], 404);
    }

    return response()->json([
        'cashier_id' => $cashier->id,
        'restaurant_id' => $restaurant->id,
        'user_id' => $restaurant->user_id,
        'token' => $cashier->token,
    ]);
}

public function kitchenContext(Request $request)
{
    $user = $request->user();

    if (! $user || ! ($user->hasRole('kitchen') || $user->hasRole('admin'))) {
        return response()->json(['message' => 'Forbidden. Kitchen or admin role required.'], 403);
    }

    $validated = $request->validate([
        'restaurant_id' => 'nullable|integer|exists:restaurants,id',
    ]);

    $selectedRestaurantId = $validated['restaurant_id'] ?? null;

    $restaurantQuery = Restaurant::with('kitchens');

    if ($user->hasRole('admin')) {
        if ($selectedRestaurantId) {
            $restaurantQuery->where('id', $selectedRestaurantId);
        }
    } else {
        $restaurantQuery->where('user_id', $user->id);
    }

    $restaurant = $restaurantQuery->first();

    if (! $restaurant) {
        return response()->json(['message' => 'No restaurant found for this kitchen context.'], 404);
    }

    $kitchen = $restaurant->kitchens->first();

    if (! $kitchen) {
        return response()->json(['message' => 'No kitchen profile found for this account.'], 404);
    }

    return response()->json([
        'kitchen_id' => $kitchen->id,
        'restaurant_id' => $restaurant->id,
        'user_id' => $restaurant->user_id,
        'token' => $kitchen->token,
    ]);
}


    public function user(Request $request)
{
    return response()->json(['user' => new UserResource($request->user())]);
}

}
