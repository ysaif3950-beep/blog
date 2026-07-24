<?php

namespace Tests\Feature\Web\Tag;

use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EditTest extends TestCase
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

    public function test_guest_is_redirected_to_login_when_visiting_tags_edit(): void
    {
        $tag = Tag::factory()->create();

        $response = $this->get(route('tags.edit', $tag));

        $response->assertRedirect(route('login'));
    }

    public function test_regular_user_is_forbidden_from_visiting_tags_edit(): void
    {
        $tag = Tag::factory()->create();

        $response = $this->actingAs($this->regularUser())->get(route('tags.edit', $tag));

        $response->assertForbidden();
    }

    public function test_admin_can_visit_tags_edit(): void
    {
        $tag = Tag::factory()->create();

        $response = $this->actingAs($this->admin())->get(route('tags.edit', $tag));

        $response->assertOk();
    }

    public function test_edit_response_uses_tags_edit_view(): void
    {
        $tag = Tag::factory()->create();

        $response = $this->actingAs($this->admin())->get(route('tags.edit', $tag));

        $response->assertViewIs('tags.edit');
    }

    public function test_edit_response_has_tag_in_view(): void
    {
        $tag = Tag::factory()->create();

        $response = $this->actingAs($this->admin())->get(route('tags.edit', $tag));

        $response->assertViewHas('tag');
    }
}
