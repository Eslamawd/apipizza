<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\PersonalAccessToken;

uses(RefreshDatabase::class);

it('issues a sanctum token for mobile login', function () {
    $user = User::factory()->create([
        'password' => Hash::make('password123'),
    ]);

    $response = $this->postJson('/api/mobile/login', [
        'email' => $user->email,
        'password' => 'password123',
        'device_name' => 'pest-suite',
    ]);

    $response->assertOk()
        ->assertJsonStructure([
            'token',
            'user' => ['id', 'name', 'email', 'role', 'balance'],
        ]);

    $this->withHeader('Authorization', 'Bearer '.$response->json('token'))
        ->getJson('/api/mobile/user')
        ->assertOk()
        ->assertJsonPath('user.email', $user->email);
});

it('revokes the current mobile token on logout', function () {
    $user = User::factory()->create();
    $token = $user->createToken('pest-suite')->plainTextToken;
    $tokenId = PersonalAccessToken::findToken($token)?->id;

    $this->withHeader('Authorization', 'Bearer '.$token)
        ->postJson('/api/mobile/logout')
        ->assertOk()
        ->assertJsonPath('message', 'Logged out successfully');

    expect($tokenId)->not->toBeNull();

    $this->assertDatabaseMissing('personal_access_tokens', [
        'id' => $tokenId,
    ]);
});