<?php

namespace Tests\Feature\River;

use App\Models\River;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RiverWishlistTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_list_only_their_wishlist_rivers(): void
    {
        $user = $this->authenticateUser();
        $otherUser = User::factory()->create();
        $favoriteRiver = River::factory()->create();
        $otherFavoriteRiver = River::factory()->create();
        $user->wishlistRivers()->attach($favoriteRiver->id);
        $otherUser->wishlistRivers()->attach($otherFavoriteRiver->id);

        $response = $this
            ->withHeader('Origin', config('app.url'))
            ->getJson('/api/wishlist/rivers');

        $response
            ->assertOk()
            ->assertJsonCount(1, 'data.rivers')
            ->assertJsonPath('data.rivers.0.id', $favoriteRiver->id);
    }

    public function test_adding_a_river_to_wishlist_is_idempotent(): void
    {
        $user = $this->authenticateUser();
        $river = River::factory()->create();

        $this
            ->withHeader('Origin', config('app.url'))
            ->postJson("/api/wishlist/rivers/{$river->id}")
            ->assertCreated()
            ->assertJsonPath('data.riverId', $river->id);

        $this
            ->withHeader('Origin', config('app.url'))
            ->postJson("/api/wishlist/rivers/{$river->id}")
            ->assertOk();

        $this->assertDatabaseCount('river_wishlists', 1);
        $this->assertDatabaseHas('river_wishlists', [
            'user_id' => $user->id,
            'river_id' => $river->id,
        ]);
    }

    public function test_user_can_remove_a_river_from_wishlist(): void
    {
        $user = $this->authenticateUser();
        $river = River::factory()->create();
        $user->wishlistRivers()->attach($river->id);

        $this
            ->withHeader('Origin', config('app.url'))
            ->deleteJson("/api/wishlist/rivers/{$river->id}")
            ->assertNoContent();

        $this->assertDatabaseMissing('river_wishlists', [
            'user_id' => $user->id,
            'river_id' => $river->id,
        ]);
    }

    public function test_guest_cannot_access_wishlist_endpoints(): void
    {
        $river = River::factory()->create();

        $this->getJson('/api/wishlist/rivers')->assertUnauthorized();
        $this->postJson("/api/wishlist/rivers/{$river->id}")->assertUnauthorized();
        $this->deleteJson("/api/wishlist/rivers/{$river->id}")->assertUnauthorized();
    }
}
