<?php

namespace Tests\Feature\Web\Post;

use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ShowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->create());
    }

    public function test_guest_cannot_view_post(): void
    {
        $post = Post::factory()->create();
        auth()->logout();

        $response = $this->get(route('posts.show', $post));
        $response->assertRedirect(route('login'));
    }

    public function test_can_view_post(): void
    {
        $post = Post::factory()->create();

        $response = $this->get(route('posts.show', $post));

        $response->assertOk();
        $response->assertViewHas('post');
        $response->assertViewIs('posts.show');
        $response->assertSee($post->title);
    }

    public function test_returns_404_for_non_existent_post(): void
    {
        $response = $this->get('/posts/99999');
        $response->assertNotFound();
    }
}
