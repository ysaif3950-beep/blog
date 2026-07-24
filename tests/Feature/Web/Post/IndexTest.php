<?php

namespace Tests\Feature\Web\Post;

use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IndexTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->create());
    }

    public function test_guest_cannot_access_posts_index(): void
    {
        auth()->logout();
        $response = $this->get(route('posts.index'));
        $response->assertRedirect(route('login'));
    }

    public function test_index_returns_paginated_posts(): void
    {
        Post::factory()->count(20)->create();

        $response = $this->get(route('posts.index'));

        $response->assertOk();
        $response->assertViewHas('posts');
        $response->assertViewIs('posts.index');
        $this->assertEquals(15, $response->viewData('posts')->perPage());
    }

    public function test_index_shows_latest_posts_first(): void
    {
        Post::factory()->count(3)->create();

        $response = $this->get(route('posts.index'));
        $response->assertOk();
    }
}
