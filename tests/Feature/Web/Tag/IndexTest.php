<?php

namespace Tests\Feature\Web\Tag;

use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IndexTest extends TestCase
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

    public function test_guest_is_redirected_to_login_when_visiting_tags_index(): void
    {
        $response = $this->get(route('tags.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_regular_user_can_visit_tags_index(): void
    {
        $response = $this->actingAs($this->regularUser())->get(route('tags.index'));

        $response->assertOk();
    }

    public function test_admin_can_visit_tags_index(): void
    {
        $response = $this->actingAs($this->admin())->get(route('tags.index'));

        $response->assertOk();
    }

    public function test_index_response_uses_tags_index_view(): void
    {
        $response = $this->actingAs($this->admin())->get(route('tags.index'));

        $response->assertViewIs('tags.index');
    }

    public function test_regular_user_sees_only_their_tags(): void
    {
        $user = $this->regularUser();
        $otherUser = User::factory()->create();

        $userTag = Tag::factory()->create(['name' => 'User Tag', 'user_id' => $user->id]);
        $otherTag = Tag::factory()->create(['name' => 'Other User Tag', 'user_id' => $otherUser->id]);

        $response = $this->actingAs($user)->get(route('tags.index'));

        $response->assertSeeText('User Tag');
        $response->assertDontSeeText('Other User Tag');
    }

    public function test_admin_sees_all_tags(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        Tag::factory()->create(['name' => 'Tag One', 'user_id' => $user1->id]);
        Tag::factory()->create(['name' => 'Tag Two', 'user_id' => $user2->id]);

        $response = $this->actingAs($this->admin())->get(route('tags.index'));

        $response->assertSeeText('Tag One');
        $response->assertSeeText('Tag Two');
    }
}
