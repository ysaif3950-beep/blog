<?php

namespace Tests\Feature\Web\User;

use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PostsTest extends TestCase
{
    use RefreshDatabase;

    protected function regularUser(): User
    {
        return User::factory()->create(['role' => 'user']);
    }

    protected function admin(): User
    {
        return User::factory()->create(['role' => 'admin']);
    }

    public function test_guest_is_redirected_to_login_when_visiting_users_posts(): void
    {
        $user = $this->regularUser();

        $response = $this->get(route('users.posts', $user->id));

        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_regular_user_can_view_their_own_posts_page(): void
    {
        $user = $this->regularUser();

        $response = $this->actingAs($user)->get(route('users.posts', $user->id));

        $response->assertOk();
    }

    public function test_authenticated_regular_user_can_view_another_users_posts_page(): void
    {
        $user = $this->regularUser();
        $otherUser = $this->regularUser();

        $response = $this->actingAs($user)->get(route('users.posts', $otherUser->id));

        $response->assertOk();
    }

    public function test_admin_can_view_another_users_posts_page(): void
    {
        $admin = $this->admin();
        $user = $this->regularUser();

        $response = $this->actingAs($admin)->get(route('users.posts', $user->id));

        $response->assertOk();
    }

    public function test_posts_response_uses_users_posts_view(): void
    {
        $user = $this->regularUser();

        $response = $this->actingAs($user)->get(route('users.posts', $user->id));

        $response->assertViewIs('users.posts');
    }

    public function test_posts_view_receives_the_target_user(): void
    {
        $user = $this->regularUser();

        $response = $this->actingAs($user)->get(route('users.posts', $user->id));

        $response->assertViewHas('user', $user);
    }

    public function test_posts_page_includes_posts_belonging_to_target_user(): void
    {
        $targetUser = $this->regularUser();
        $otherUser = $this->regularUser();

        $post = Post::factory()->create(['user_id' => $targetUser->id]);
        Post::factory()->create(['user_id' => $otherUser->id]);

        $response = $this->actingAs($targetUser)->get(route('users.posts', $targetUser->id));

        $responseUser = $response->viewData('user');
        $this->assertTrue($responseUser->posts->contains($post));
    }

    public function test_requesting_missing_user_id_returns_404(): void
    {
        $user = $this->regularUser();

        $response = $this->actingAs($user)->get(route('users.posts', 999999));

        $response->assertNotFound();
    }
}
