<?php

namespace Tests\Feature\Web\User;

use App\Models\Post;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    protected function createUser(): User
    {
        return User::factory()->create(['role' => 'user']);
    }

    protected function createAdmin(): User
    {
        return User::factory()->create(['role' => 'admin']);
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $response = $this->get(route('users.profile'));

        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_view_their_profile(): void
    {
        $user = $this->createUser();

        $response = $this->actingAs($user)->get(route('users.profile'));

        $response->assertOk();
    }

    public function test_profile_response_uses_users_profile_view(): void
    {
        $user = $this->createUser();

        $response = $this->actingAs($user)->get(route('users.profile'));

        $response->assertViewIs('users.profile');
    }

    public function test_profile_view_receives_the_authenticated_user(): void
    {
        $user = $this->createUser();

        $response = $this->actingAs($user)->get(route('users.profile'));

        $response->assertViewHas('user');
        $this->assertTrue($response->viewData('user')->is($user));
    }

    public function test_profile_view_has_users_posts(): void
    {
        $user = $this->createUser();
        Post::factory()->count(3)->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->get(route('users.profile'));

        $response->assertViewHas('posts');
        $posts = $response->viewData('posts');
        $this->assertCount(3, $posts);
    }

    public function test_profile_view_has_users_tags(): void
    {
        $user = $this->createUser();
        Tag::factory()->count(3)->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->get(route('users.profile'));

        $response->assertViewHas('tags');
        $tags = $response->viewData('tags');
        $this->assertCount(3, $tags);
    }

    public function test_profile_posts_are_paginated_by_6(): void
    {
        $user = $this->createUser();
        Post::factory()->count(10)->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->get(route('users.profile'));

        $response->assertViewHas('posts');
        $posts = $response->viewData('posts');
        $this->assertEquals(6, $posts->perPage());
        $this->assertEquals('posts_page', $posts->getPageName());
    }

    public function test_profile_tags_are_paginated_by_12(): void
    {
        $user = $this->createUser();
        Tag::factory()->count(15)->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->get(route('users.profile'));

        $response->assertViewHas('tags');
        $tags = $response->viewData('tags');
        $this->assertEquals(12, $tags->perPage());
        $this->assertEquals('tags_page', $tags->getPageName());
    }

    public function test_profile_posts_are_ordered_by_latest(): void
    {
        $user = $this->createUser();
        $oldPost = Post::factory()->create(['user_id' => $user->id, 'created_at' => now()->subDays(2)]);
        $newPost = Post::factory()->create(['user_id' => $user->id, 'created_at' => now()]);

        $response = $this->actingAs($user)->get(route('users.profile'));

        $posts = $response->viewData('posts');
        $this->assertTrue($posts->first()->is($newPost));
    }

    public function test_profile_only_shows_authenticated_users_posts(): void
    {
        $user = $this->createUser();
        $anotherUser = $this->createUser();

        Post::factory()->count(3)->create(['user_id' => $user->id]);
        Post::factory()->count(5)->create(['user_id' => $anotherUser->id]);

        $response = $this->actingAs($user)->get(route('users.profile'));

        $posts = $response->viewData('posts');
        $this->assertCount(3, $posts);
    }

    public function test_profile_only_shows_authenticated_users_tags(): void
    {
        $user = $this->createUser();
        $anotherUser = $this->createUser();

        Tag::factory()->count(2)->create(['user_id' => $user->id]);
        Tag::factory()->count(4)->create(['user_id' => $anotherUser->id]);

        $response = $this->actingAs($user)->get(route('users.profile'));

        $tags = $response->viewData('tags');
        $this->assertCount(2, $tags);
    }

    public function test_profile_tags_have_post_count(): void
    {
        $user = $this->createUser();
        $tag = Tag::factory()->create(['user_id' => $user->id]);
        $posts = Post::factory()->count(3)->create(['user_id' => $user->id]);
        $tag->posts()->attach($posts->pluck('id'));

        $response = $this->actingAs($user)->get(route('users.profile'));

        $tags = $response->viewData('tags');
        $loadedTag = $tags->firstWhere('id', $tag->id);
        $this->assertNotNull($loadedTag);
        $this->assertEquals(3, $loadedTag->posts_count);
    }

    public function test_admin_can_view_their_profile(): void
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin)->get(route('users.profile'));

        $response->assertOk();
        $response->assertViewIs('users.profile');
    }
}

