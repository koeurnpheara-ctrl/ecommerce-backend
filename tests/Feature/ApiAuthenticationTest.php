<?php

namespace Tests\Feature;

use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_register_and_receive_token(): void
    {
        Role::create(['name' => 'Customer', 'slug' => 'customer']);
        $response = $this->postJson('/api/v1/auth/register', [
            'name' => 'Test Customer', 'email' => 'customer@example.com',
            'password' => 'SecurePassword123!', 'password_confirmation' => 'SecurePassword123!',
        ]);
        $response->assertCreated()->assertJsonStructure(['data' => ['user', 'token']]);
    }

    public function test_customer_can_login(): void
    {
        Role::create(['name' => 'Customer', 'slug' => 'customer']);
        $this->postJson('/api/v1/auth/register', [
            'name' => 'Test Customer', 'email' => 'customer@example.com',
            'password' => 'SecurePassword123!', 'password_confirmation' => 'SecurePassword123!',
        ])->assertCreated();

        $this->postJson('/api/v1/auth/login', [
            'email' => 'customer@example.com', 'password' => 'SecurePassword123!',
        ])->assertOk()->assertJsonStructure(['data' => ['token']]);
    }
}
