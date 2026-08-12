<?php

namespace Tests\Feature\Auth;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LogoutTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic feature test example.
     */
    public function test_user_can_logout(): void
    {
        $user = $this->authenticateUser();

        $response = $this->withHeader('Origin', config('app.url'))->postJson('/api/logout');

        $response->assertOk();

        $this->assertGuest('web');
    }

    public function test_unauthenticated_user_cannot_logout(): void
    {
        $response = $this->withHeader('Origin', config('app.url'))->postJson('/api/logout');

        $response->assertUnauthorized();
    }

}
