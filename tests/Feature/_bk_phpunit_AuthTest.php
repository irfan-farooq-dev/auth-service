<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use App\Services\JwtService;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function user_can_register_and_receive_token()
    {
        $response = $this->postJson('/api/register', [
            'name' => 'Jane Doe',
            'email' => 'jane5@example.com',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
        ]);

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'user' => ['id', 'name', 'email'],
                     'token'
                 ]);
    }

    /** @test */
    public function user_can_login_and_receive_token()
    {
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
    }

    /** @test */
    public function profile_requires_valid_token()
    {
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
    }

    /** @test */
    public function profile_fails_with_invalid_token()
    {
        $this->getJson('/api/profile', [
            'Authorization' => "Bearer invalidtoken"
        ])->assertStatus(401);
    }
}
