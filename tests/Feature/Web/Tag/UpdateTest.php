<?php

namespace Tests\Feature\Web\Tag;

use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UpdateTest extends TestCase
{
    use RefreshDatabase;

    protected function admin(): User
    {
        return User::factory()->create(['role' => 'admin']);
    }

    protected function regularUser(): User
    {
        return User::factory()->create(['role' => 'user']);
    }

    public function test_guest_is_redirected_to_login_when_updating_tag(): void
    {
        $tag = Tag::factory()->create();

        $response = $this->put(route('tags.update', $tag), [
            'name' => 'Updated Tag',
        ]);

        $response->assertRedirect(route('login'));
    }

    public function test_regular_user_is_forbidden_from_updating_tag(): void
    {
        $tag = Tag::factory()->create();

        $response = $this->actingAs($this->regularUser())->put(route('tags.update', $tag), [
            'name' => 'Updated Tag',
        ]);

        $response->assertForbidden();
    }

    public function test_admin_can_update_tag(): void
    {
        $tag = Tag::factory()->create(['name' => 'Original Name']);

        $response = $this->actingAs($this->admin())->put(route('tags.update', $tag), [
            'name' => 'Updated Name',
        ]);

        $response->assertRedirect(route('tags.index'));
        $response->assertSessionHas('success', 'Tag updated successfully.');
        $this->assertDatabaseHas('tags', [
            'id' => $tag->id,
            'name' => 'Updated Name',
        ]);
    }

    public function test_update_tag_requires_name(): void
    {
        $tag = Tag::factory()->create();

        $response = $this->actingAs($this->admin())->put(route('tags.update', $tag), []);

        $response->assertSessionHasErrors('name');
    }

    public function test_update_tag_name_must_be_at_least_3_characters(): void
    {
        $tag = Tag::factory()->create();

        $response = $this->actingAs($this->admin())->put(route('tags.update', $tag), [
            'name' => 'ab',
        ]);

        $response->assertSessionHasErrors('name');
    }

    public function test_update_tag_name_can_be_same_as_current(): void
    {
        $tag = Tag::factory()->create(['name' => 'Current Name']);

        $response = $this->actingAs($this->admin())->put(route('tags.update', $tag), [
            'name' => 'Current Name',
        ]);

        $response->assertRedirect(route('tags.index'));
        $this->assertDatabaseHas('tags', [
            'id' => $tag->id,
            'name' => 'Current Name',
        ]);
    }

    public function test_update_tag_name_must_be_unique_except_self(): void
    {
        $tag1 = Tag::factory()->create(['name' => 'Tag One']);
        $tag2 = Tag::factory()->create(['name' => 'Tag Two']);

        $response = $this->actingAs($this->admin())->put(route('tags.update', $tag1), [
            'name' => 'Tag Two',
        ]);

        $response->assertSessionHasErrors('name');
    }
}
