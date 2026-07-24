<?php

namespace Tests\Feature\Web\Post;

use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DestroyTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected User $admin;
    protected Post $post;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->admin = User::factory()->admin()->create();
        $this->post = Post::factory()->create(['user_id' => $this->user->id]);
        $this->actingAs($this->user);
    }

    public function test_guest_cannot_delete_post(): void
    {
        auth()->logout();
        $response = $this->delete(route('posts.destroy', $this->post));
        $response->assertRedirect(route('login'));
    }

    public function test_owner_can_delete_post(): void
    {
        $response = $this->delete(route('posts.destroy', $this->post));

        $response->assertRedirect();
        $response->assertSessionHas('success');
        $this->assertModelMissing($this->post);
    }

    public function test_non_owner_non_admin_cannot_delete_post(): void
    {
        $otherUser = User::factory()->create();
        $this->actingAs($otherUser);

        $response = $this->delete(route('posts.destroy', $this->post));
        $response->assertForbidden();
    }

    public function test_admin_can_delete_any_post(): void
    {
        $this->actingAs($this->admin);

        $response = $this->delete(route('posts.destroy', $this->post));
        $response->assertRedirect();
        $response->assertSessionHas('success');
        $this->assertModelMissing($this->post);
    }
}
