<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use App\Services\JwtService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('allows a user to register and receive a token', function () {
    $response = $this->postJson('/api/register', [
        'name' => 'Jane Doe',
        'email' => 'jane@example.com',
        'password' => 'secret123',
        'password_confirmation' => 'secret123',
    ]);

    $response->assertStatus(200)
             ->assertJsonStructure([
                 'user' => ['id', 'name', 'email'],
                 'token'
             ]);
});

it('allows a user to login and receive a token', function () {
    $user = User::factory()->create([
        'email' => 'jane@example.com',
        'password' => Hash::make('secret123'),
    ]);

    $response = $this->postJson('/api/login', [
        'email' => 'jane@example.com',
        'password' => 'secret123',
    ]);

    $response->assertStatus(200)
             ->assertJsonStructure([
                 'user' => ['id', 'name', 'email'],
                 'token'
             ]);
});

it('requires a valid token to access profile', function () {
    $user = User::factory()->create([
        'email' => 'jane@example.com',
        'password' => Hash::make('secret123'),
    ]);

    $token = JwtService::generateToken($user->id);

    // Without token → should fail
    $this->getJson('/api/profile')
         ->assertStatus(401);

    // With valid token → should succeed
    $this->getJson('/api/profile', [
        'Authorization' => "Bearer {$token}"
    ])->assertStatus(200)
      ->assertJson([
          'id' => $user->id,
          'email' => $user->email,
      ]);
});

it('rejects invalid token on profile route', function () {
    $this->getJson('/api/profile', [
        'Authorization' => "Bearer invalidtoken"
    ])->assertStatus(401);
});
