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
        $response
            ->assertCreated()
            ->assertJsonPath('message', 'Cadastro realizado com sucesso')
            ->assertJsonPath('data.user.name', 'Hellen')
            ->assertJsonPath('data.user.email', 'hellen@example.com')
            ->assertJsonPath('data.user.bio', null)
            ->assertJsonPath('data.user.homeRiver', null)
            ->assertJsonPath('data.user.avatarUrl', null)
            ->assertJsonPath('data.user.coverUrl', null)
            ->assertJsonStructure([
                'data' => [
                    'user' => [
                        'id',
                        'name',
                        'email',
                        'bio',
                        'homeRiver',
                        'avatarUrl',
                        'coverUrl',
                    ],
                ],
            ]);

        $this->assertDatabaseHas('users', [
            'email' => 'hellen@example.com',
        ]);
    }

    public function test_user_cannot_register_without_email(): void
    {
        // Arrange
        $data = [
            'name' => 'Hellen',
            'email' => '',
            'password' => '12345678',
            'password_confirmation' => '12345678',
        ];

        // Act
        $response = $this->postJson('/api/register', $data);

        // Assert
        $response->assertUnprocessable()->assertInvalid(['email']);

    }
}
