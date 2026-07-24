<?php

namespace Tests\Feature\Web\Post;

use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HomeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->create());
    }

    public function test_guest_cannot_access_home(): void
    {
        auth()->logout();
        $response = $this->get(route('dashboard'));
        $response->assertRedirect(route('login'));
    }

    public function test_home_returns_paginated_posts(): void
    {
        Post::factory()->count(20)->create();

        $response = $this->get(route('dashboard'));

        $response->assertOk();
        $response->assertViewHas('posts');
        $this->assertEquals(15, $response->viewData('posts')->perPage());
    }
}
