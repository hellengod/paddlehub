<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;
    public function test_authenticated_user_can_get_own_data(): void
    {
        $user = $this->authenticateUser();

        $response = $this
            ->withHeader('Origin', config('app.url'))
            ->getJson('/api/user');

        $response
            ->assertOk()
            ->assertJson([
                'message' => 'Usuário autenticado recuperado com sucesso',
                'data' => [
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'bio' => null,
                        'homeRiver' => null,
                        'avatarUrl' => null,
                        'coverUrl' => null,
                    ],
                ],
            ])
            ->assertJsonStructure([
                'message',
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
    }

    public function test_guest_cannot_get_user_data(): void
    {
        $response = $this
            ->withHeader('Origin', config('app.url'))
            ->getJson('/api/user');

        $response->assertUnauthorized();
    }
}
