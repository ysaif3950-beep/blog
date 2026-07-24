<?php

namespace Tests\Feature\Web\Tag;

use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DestroyTest extends TestCase
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

    public function test_guest_is_redirected_to_login_when_deleting_tag(): void
    {
        $tag = Tag::factory()->create();

        $response = $this->delete(route('tags.destroy', $tag));

        $response->assertRedirect(route('login'));
    }

    public function test_regular_user_is_forbidden_from_deleting_tag(): void
    {
        $tag = Tag::factory()->create();

        $response = $this->actingAs($this->regularUser())->delete(route('tags.destroy', $tag));

        $response->assertForbidden();
    }

    public function test_admin_can_delete_tag(): void
    {
        $tag = Tag::factory()->create();

        $response = $this->actingAs($this->admin())->delete(route('tags.destroy', $tag));

        $response->assertRedirect(route('tags.index'));
        $response->assertSessionHas('success', 'Tag deleted successfully.');
        $this->assertDatabaseMissing('tags', [
            'id' => $tag->id,
        ]);
    }

    public function test_tag_is_actually_deleted_from_database(): void
    {
        $tag = Tag::factory()->create();

        $this->actingAs($this->admin())->delete(route('tags.destroy', $tag));

        $this->assertDatabaseMissing('tags', ['id' => $tag->id]);
        $this->assertEquals(0, Tag::count());
    }
}
