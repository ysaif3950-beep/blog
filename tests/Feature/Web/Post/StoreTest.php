<?php

namespace Tests\Feature\Web\Post;

use App\Models\Post;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class StoreTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->actingAs($this->user);
    }

    public function test_guest_cannot_store_post(): void
    {
        auth()->logout();
        $response = $this->post(route('posts.store'), [
            'title' => 'Test Title',
            'description' => 'Test description content here',
        ]);
        $response->assertRedirect(route('login'));
    }

    public function test_store_post_with_valid_data(): void
    {
        Storage::fake('public');
        $tag = Tag::factory()->create();

        $file = UploadedFile::fake()->image('post.jpg');

        $response = $this->post(route('posts.store'), [
            'title' => 'Test Post Title',
            'description' => 'This is a test description with enough characters',
            'image' => $file,
            'tags' => [$tag->id],
        ]);

        $response->assertRedirect(route('posts.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('posts', [
            'title' => 'Test Post Title',
            'description' => 'This is a test description with enough characters',
            'user_id' => $this->user->id,
        ]);

        $post = Post::first();
        Storage::disk('public')->assertExists($post->image);
        $this->assertTrue($post->tags->contains($tag));
    }

    public function test_store_post_without_image(): void
    {
        $response = $this->post(route('posts.store'), [
            'title' => 'Post Without Image',
            'description' => 'This post has no image attached to it',
        ]);

        $response->assertRedirect(route('posts.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('posts', [
            'title' => 'Post Without Image',
            'description' => 'This post has no image attached to it',
            'user_id' => $this->user->id,
        ]);
    }

    public function test_store_post_validation_fails(): void
    {
        $response = $this->post(route('posts.store'), [
            'title' => '',
            'description' => '',
        ]);

        $response->assertSessionHasErrors(['title', 'description']);
    }

    public function test_store_post_with_tags(): void
    {
        Storage::fake('public');
        $tags = Tag::factory()->count(3)->create();
        $tagIds = $tags->pluck('id')->toArray();

        $response = $this->post(route('posts.store'), [
            'title' => 'Post With Multiple Tags',
            'description' => 'This post has multiple tags associated with it',
            'tags' => $tagIds,
        ]);

        $response->assertRedirect(route('posts.index'));

        $post = Post::first();
        $this->assertCount(3, $post->tags);
        foreach ($tagIds as $tagId) {
            $this->assertTrue($post->tags->contains($tagId));
        }
    }
}
