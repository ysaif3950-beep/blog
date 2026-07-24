<?php

namespace Tests\Feature\Web\Post;

use App\Models\Post;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EditTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected User $otherUser;
    protected Post $post;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->otherUser = User::factory()->create();
        $this->post = Post::factory()->create(['user_id' => $this->user->id]);
        $this->actingAs($this->user);
    }

    public function test_guest_cannot_access_edit(): void
    {
        auth()->logout();
        $response = $this->get(route('posts.edit', $this->post));
        $response->assertRedirect(route('login'));
    }

    public function test_owner_can_access_edit(): void
    {
        Tag::factory()->count(3)->create();

        $response = $this->get(route('posts.edit', $this->post));

        $response->assertOk();
        $response->assertViewIs('posts.edit');
        $response->assertViewHas('post');
        $response->assertViewHas('tags');
        $response->assertViewHas('users');
    }

    public function test_non_owner_cannot_access_edit(): void
    {
        $this->actingAs($this->otherUser);

        $response = $this->get(route('posts.edit', $this->post));
        $response->assertForbidden();
    }
}
