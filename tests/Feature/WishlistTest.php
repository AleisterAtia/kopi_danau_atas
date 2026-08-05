<?php

namespace Tests\Feature;

use App\Models\TourPackage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WishlistTest extends TestCase
{
    use RefreshDatabase;

    public function test_toggling_twice_adds_then_removes_the_package(): void
    {
        $user = User::factory()->create();
        $package = TourPackage::factory()->create();

        $this->actingAs($user)
            ->postJson(route('wishlist.toggle', $package))
            ->assertOk()
            ->assertJson(['wishlisted' => true]);

        $this->assertTrue($user->favoritePackages()->where('tour_package_id', $package->id)->exists());

        $this->actingAs($user)
            ->postJson(route('wishlist.toggle', $package))
            ->assertOk()
            ->assertJson(['wishlisted' => false]);

        $this->assertFalse($user->favoritePackages()->where('tour_package_id', $package->id)->exists());
    }

    public function test_guest_is_redirected_to_login_when_toggling(): void
    {
        $package = TourPackage::factory()->create();

        $this->post(route('wishlist.toggle', $package))
            ->assertRedirect(route('login'));
    }

    public function test_wishlist_page_only_lists_the_current_users_favorites(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        $mine = TourPackage::factory()->create(['is_active' => true]);
        $theirs = TourPackage::factory()->create(['is_active' => true]);

        $user->favoritePackages()->attach($mine->id);
        $otherUser->favoritePackages()->attach($theirs->id);

        $response = $this->actingAs($user)->get(route('wishlist.index'));

        $response->assertOk();
        $response->assertSee($mine->name);
        $response->assertDontSee($theirs->name);
    }
}
