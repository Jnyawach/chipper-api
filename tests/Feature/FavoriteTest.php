<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Post;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FavoriteTest extends TestCase
{
    use DatabaseMigrations;

    public function test_a_user_can_get_their_favorites()
    {
        $user = User::factory()->create();
        $anotherUser = User::factory()->create();
        $post = Post::factory()->create();

        $this->actingAs($user)
            ->postJson(route('favorites.store', ['post' => $post]))
            ->assertCreated();
        $this->actingAs($user)
            ->postJson(route('favorites.user.store', ['user' => $anotherUser->id]));

        $this->actingAs($user)
            ->getJson(route('favorites.index'))
            ->assertOk()
            ->assertJsonFragment([
                'id' => $post->id,
                'title' => $post->title,
                'body' => $post->body,
            ])
            ->assertJsonFragment([
                'id' => $anotherUser->id,
                'name' => $anotherUser->name,
                'email' => $anotherUser->email,
            ]);


    }

    public function test_a_guest_can_not_favorite_a_post()
    {
        $post = Post::factory()->create();

        $this->postJson(route('favorites.store', ['post' => $post]))
            ->assertStatus(401);
    }


    public function test_a_user_can_favorite_a_post()
    {
        $user = User::factory()->create();
        $post = Post::factory()->create();

        $this->actingAs($user)
            ->postJson(route('favorites.store', ['post' => $post]))
            ->assertCreated();

        $this->assertDatabaseHas('favorites', [
            'favorite_id' => $post->id,
            'favorite_type' => Post::class,
            'user_id' => $user->id,
        ]);
    }

    public function test_a_user_can_remove_a_post_from_his_favorites()
    {
        $user = User::factory()->create();
        $post = Post::factory()->create();

        $this->actingAs($user)
            ->postJson(route('favorites.store', ['post' => $post]))
            ->assertCreated();

        $this->assertDatabaseHas('favorites', [
            'favorite_id' => $post->id,
            'favorite_type' => Post::class,
            'user_id' => $user->id,
        ]);

        $this->actingAs($user)
            ->deleteJson(route('favorites.destroy', ['post' => $post]))
            ->assertNoContent();

        $this->assertDatabaseMissing('favorites', [
            'favorite_id' => $post->id,
            'favorite_type' => Post::class,
            'user_id' => $user->id
        ]);
    }

    public function test_a_user_can_not_remove_a_non_favorited_item()
    {
        $user = User::factory()->create();
        $post = Post::factory()->create();

        $this->actingAs($user)
            ->deleteJson(route('favorites.destroy', ['post' => $post]))
            ->assertNotFound();
    }

    public function test_a_user_can_favorite_another_user()
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        $this->actingAs($user)
            ->postJson(route('favorites.user.store', ['user' => $otherUser->id]))
            ->assertCreated();

        $this->assertDatabaseHas('favorites', [
            'favorite_id' => $otherUser->id,
            'favorite_type' => User::class,
            'user_id' => $user->id,
        ]);
    }

    public function test_a_user_can_unfavorite_another_user()
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        $this->actingAs($user)
            ->postJson(route('favorites.user.store', ['user' => $otherUser->id]))
            ->assertCreated();

        $this->assertDatabaseHas('favorites', [
            'favorite_id' => $otherUser->id,
            'favorite_type' => User::class,
            'user_id' => $user->id,
        ]);

        $this->actingAs($user)
            ->deleteJson(route('favorites.user.destroy', ['user' => $otherUser->id]))
            ->assertNoContent();

        $this->assertDatabaseMissing('favorites', [
            'favorite_id' => $otherUser->id,
            'favorite_type' => User::class,
            'user_id' => $user->id,
        ]);
    }

    public function test_a_user_cannot_favorite_themselves()
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->postJson(route('favorites.user.store', ['user' => $user->id]))
            ->assertStatus(400)
            ->assertJson(['message' => 'You cannot favorite yourself.']);
    }
}
