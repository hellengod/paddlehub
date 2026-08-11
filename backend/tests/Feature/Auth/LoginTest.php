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
        $response->assertOk();

        $this->assertAuthenticatedAs($user);
    }
}
