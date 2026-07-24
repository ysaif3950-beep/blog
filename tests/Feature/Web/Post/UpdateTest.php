<?php

namespace Tests\Feature\Web\Post;

use App\Models\Post;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class UpdateTest extends TestCase
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

    public function test_guest_cannot_update_post(): void
    {
        auth()->logout();
        $response = $this->put(route('posts.update', $this->post), [
            'title' => 'Updated Title',
            'description' => 'Updated description with enough characters',
        ]);
        $response->assertRedirect(route('login'));
    }

    public function test_owner_can_update_post(): void
    {
        $response = $this->put(route('posts.update', $this->post), [
            'title' => 'Updated Title',
            'description' => 'Updated description with enough characters now',
        ]);

        $response->assertRedirect(route('posts.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('posts', [
            'id' => $this->post->id,
            'title' => 'Updated Title',
            'description' => 'Updated description with enough characters now',
        ]);
    }

    public function test_non_owner_cannot_update_post(): void
    {
        $this->actingAs($this->otherUser);

        $response = $this->put(route('posts.update', $this->post), [
            'title' => 'Updated Title',
            'description' => 'Updated description with enough characters',
        ]);

        $response->assertForbidden();
    }

    public function test_update_post_with_new_image_deletes_old(): void
    {
        Storage::fake('public');

        $oldImage = UploadedFile::fake()->image('old.jpg');
        $this->post->update([
            'image' => $oldImage->store('uploads', 'public'),
        ]);
        $oldPath = $this->post->fresh()->image;

        $newImage = UploadedFile::fake()->image('new.jpg');
        $response = $this->put(route('posts.update', $this->post), [
            'title' => 'Updated Title',
            'description' => 'Updated description with enough characters',
            'image' => $newImage,
        ]);

        $response->assertRedirect(route('posts.index'));

        Storage::disk('public')->assertMissing($oldPath);

        $post = $this->post->fresh();
        Storage::disk('public')->assertExists($post->image);
    }

    public function test_update_post_syncs_tags(): void
    {
        $tags = Tag::factory()->count(3)->create();
        $this->post->tags()->sync($tags->take(2)->pluck('id'));

        $newTags = [$tags[2]->id];

        $response = $this->put(route('posts.update', $this->post), [
            'title' => 'Updated Title',
            'description' => 'Updated description with enough characters',
            'tags' => $newTags,
        ]);

        $response->assertRedirect(route('posts.index'));

        $this->post->refresh();
        $this->assertEquals(1, $this->post->tags->count());
        $this->assertTrue($this->post->tags->contains($tags[2]));
    }

    public function test_update_post_validation_fails(): void
    {
        $response = $this->put(route('posts.update', $this->post), [
            'title' => '',
            'description' => '',
        ]);

        $response->assertSessionHasErrors(['title', 'description']);
    }
}
