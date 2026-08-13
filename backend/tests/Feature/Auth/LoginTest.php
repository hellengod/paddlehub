<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;
    public function test_user_can_login_with_valid_credentials(): void
    {
        $user = User::factory()->create();

        // Act
        $response = $this
            ->withHeader('Origin', config('app.url'))
            ->postJson('/api/login', [
                'email' => $user->email,
                'password' => 'password',
            ]);

        // Assert
        $response
            ->assertOk()
            ->assertJson([
                'message' => 'Login realizado com sucesso',
                'data' => [
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                    ],
                ],
            ]);


        $this->assertAuthenticatedAs($user, 'web');
    }

    public function test_user_cannot_login_with_invalid_credentials(): void
    {
        $user = User::factory()->create();
        $response = $this
            ->withHeader('Origin', config('app.url'))
            ->postJson('/api/login', [
                'email' => $user->email,
                'password' => 'senha',
            ]);

        $response
            ->assertUnauthorized()
            ->assertJson([
                'message' => 'Usuario nao autenticado',
            ]);

        $this->assertGuest('web');
    }
    public function test_user_cannot_login_without_password(): void
    {
        $response = $this
            ->withHeader('Origin', config('app.url'))
            ->postJson('/api/login', [
                'email' => 'hellen@email.com',
            ]);

        $response
            ->assertUnprocessable()
            ->assertInvalid(['password']);

        $this->assertGuest('web');
    }
}
