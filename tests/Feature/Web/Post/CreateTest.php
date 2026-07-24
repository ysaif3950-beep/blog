<?php

namespace Tests\Feature\Web\Post;

use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CreateTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->create());
    }

    public function test_guest_cannot_access_create(): void
    {
        auth()->logout();
        $response = $this->get(route('posts.create'));
        $response->assertRedirect(route('login'));
    }

    public function test_create_view_has_tags(): void
    {
        Tag::factory()->count(3)->create();

        $response = $this->get(route('posts.create'));

        $response->assertOk();
        $response->assertViewIs('posts.add');
        $response->assertViewHas('tags');
        $this->assertCount(3, $response->viewData('tags'));
    }
}
