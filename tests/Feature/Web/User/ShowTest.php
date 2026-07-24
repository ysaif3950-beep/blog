<?php

namespace Tests\Feature\Web\User;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ShowTest extends TestCase
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

    public function test_guest_is_redirected_to_login_when_visiting_users_show(): void
    {
        $user = $this->regularUser();

        $response = $this->get(route('users.show', $user));

        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_regular_user_can_view_their_own_user_page(): void
    {
        $user = $this->regularUser();

        $response = $this->actingAs($user)->get(route('users.show', $user));

        $response->assertOk();
    }

    public function test_authenticated_regular_user_can_view_another_user_page(): void
    {
        $user = $this->regularUser();
        $otherUser = $this->regularUser();

        $response = $this->actingAs($user)->get(route('users.show', $otherUser));

        $response->assertOk();
    }

    public function test_admin_can_view_another_user_page(): void
    {
        $admin = $this->admin();
        $user = $this->regularUser();

        $response = $this->actingAs($admin)->get(route('users.show', $user));

        $response->assertOk();
    }

    public function test_show_response_uses_users_show_view(): void
    {
        $user = $this->regularUser();

        $response = $this->actingAs($user)->get(route('users.show', $user));

        $response->assertViewIs('users.show');
    }

    public function test_show_view_receives_the_target_user(): void
    {
        $user = $this->regularUser();

        $response = $this->actingAs($user)->get(route('users.show', $user));

        $response->assertViewHas('user', $user);
    }

    public function test_requesting_missing_user_returns_404(): void
    {
        $user = $this->regularUser();

        $response = $this->actingAs($user)->get(route('users.show', 999999));

        $response->assertNotFound();
    }
}
