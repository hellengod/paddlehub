<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class RegisterTest extends TestCase
{

    use RefreshDatabase;

    public function test_user_can_register_with_valid_data(): void
    {
        // Arrange
        $data = [
            'name' => 'Hellen',
            'email' => 'hellen@example.com',
            'password' => '12345678',
            'password_confirmation' => '12345678',
        ];

        // Act
        $response = $this->postJson('/api/register', $data);

        // Assert
        $response->assertCreated();

        $this->assertDatabaseHas('users', [
            'email' => 'hellen@example.com',
        ]);
    }
}
