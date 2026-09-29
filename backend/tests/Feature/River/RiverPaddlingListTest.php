<?php

namespace Tests\Feature\River;

use App\Models\River;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RiverPaddlingListTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_list_only_their_rivers_to_paddle(): void
    {
        $user = $this->authenticateUser();
        $otherUser = User::factory()->create();
        $riverToPaddle = River::factory()->create();
        $otherRiverToPaddle = River::factory()->create();
        $user->riversToPaddle()->attach($riverToPaddle->id);
        $otherUser->riversToPaddle()->attach($otherRiverToPaddle->id);

        $response = $this
            ->withHeader('Origin', config('app.url'))
            ->getJson('/api/paddling-list/rivers');

        $response
            ->assertOk()
            ->assertJsonCount(1, 'data.rivers')
            ->assertJsonPath('data.rivers.0.id', $riverToPaddle->id);
    }

    public function test_adding_a_river_to_paddling_list_is_idempotent(): void
    {
        $user = $this->authenticateUser();
        $river = River::factory()->create();

        $this
            ->withHeader('Origin', config('app.url'))
            ->postJson("/api/paddling-list/rivers/{$river->id}")
            ->assertCreated()
            ->assertJsonPath('data.riverId', $river->id);

        $this
            ->withHeader('Origin', config('app.url'))
            ->postJson("/api/paddling-list/rivers/{$river->id}")
            ->assertOk();

        $this->assertDatabaseCount('river_paddling_lists', 1);
        $this->assertDatabaseHas('river_paddling_lists', [
            'user_id' => $user->id,
            'river_id' => $river->id,
        ]);
    }

    public function test_user_can_remove_a_river_from_paddling_list(): void
    {
        $user = $this->authenticateUser();
        $river = River::factory()->create();
        $user->riversToPaddle()->attach($river->id);

        $this
            ->withHeader('Origin', config('app.url'))
            ->deleteJson("/api/paddling-list/rivers/{$river->id}")
            ->assertNoContent();

        $this->assertDatabaseMissing('river_paddling_lists', [
            'user_id' => $user->id,
            'river_id' => $river->id,
        ]);
    }

    public function test_guest_cannot_access_paddling_list_endpoints(): void
    {
        $river = River::factory()->create();

        $this->getJson('/api/paddling-list/rivers')->assertUnauthorized();
        $this->postJson("/api/paddling-list/rivers/{$river->id}")->assertUnauthorized();
        $this->deleteJson("/api/paddling-list/rivers/{$river->id}")->assertUnauthorized();
    }
}
