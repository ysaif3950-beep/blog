<?php

namespace Tests\Feature\Web\Post;

use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SearchTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->create());
    }

    public function test_search_by_title_returns_matching_posts(): void
    {
        Post::factory()->create(['title' => 'Unique Search Title']);
        Post::factory()->count(3)->create();

        $response = $this->get('/posts/search?search=Unique+Search');

        $response->assertOk();
        $response->assertViewHas('posts');
        $response->assertViewIs('posts.search');
        $this->assertEquals(1, $response->viewData('posts')->total());
    }

    public function test_search_by_description_returns_matching_posts(): void
    {
        Post::factory()->create(['description' => 'This is a unique description for testing']);
        Post::factory()->count(3)->create();

        $response = $this->get('/posts/search?search=unique+description');

        $response->assertOk();
        $this->assertGreaterThanOrEqual(1, $response->viewData('posts')->total());
    }

    public function test_empty_search_returns_all_posts(): void
    {
        Post::factory()->count(10)->create();

        $response = $this->get('/posts/search?search=');

        $response->assertOk();
        $this->assertEquals(10, $response->viewData('posts')->total());
    }

    public function test_search_posts_are_paginated(): void
    {
        Post::factory()->count(20)->create(['title' => 'Searchable Post']);

        $response = $this->get('/posts/search?search=Searchable');

        $response->assertOk();
        $this->assertEquals(15, $response->viewData('posts')->perPage());
    }
}
