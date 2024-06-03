<?php

namespace Tests\Unit;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthControllerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_registers_a_user()
    {
        $response = $this->postJson('/api/auth/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password'
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('users', ['email' => 'test@example.com']);
    }

    /** @test */
    public function it_logs_in_a_user()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password')
        ]);

        $response = $this->postJson('/api/auth/login', [
            'email' => 'test@example.com',
            'password' => 'password'
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure(['access_token', 'token_type', 'expires_in', 'user']);
        $token = $response->json('access_token');

        $this->assertNotEmpty($token);
    }

    /** @test */
    public function it_logs_out_a_user()
    {
        $user = User::factory()->create([
            'password' => Hash::make('password')
        ]);

        // Autenticar al usuario y obtener el token
        $loginResponse = $this->postJson('/api/auth/login', [
            'email' => $user->email,
            'password' => 'password'
        ]);

        $loginResponse->assertStatus(200);
        $token = $loginResponse->json('access_token');

        // // Verificar que el token es válido
         $this->assertNotEmpty($token);

        // // Intentar cerrar sesión con el token
        $response = $this->postJson('/api/auth/logout', [], [
            'Authorization' => "Bearer $token"
        ]);

        $response->assertStatus(200);
        // $response->assertJson(['message' => 'User successfully signed out']);
    }
}
