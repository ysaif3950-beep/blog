<?php

namespace Tests\Feature\Web\User;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DestroySelfTest extends TestCase
{
    use RefreshDatabase;

    protected function regularUser(): User
    {
        return User::factory()->create(['role' => 'user']);
    }

    public function test_guest_is_redirected_to_login_when_deleting_their_profile(): void
    {
        $response = $this->delete(route('users.profile.destroy'));

        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_delete_their_own_account(): void
    {
        $user = $this->regularUser();

        $response = $this->actingAs($user)
            ->delete(route('users.profile.destroy'));

        $response->assertRedirect(route('home'));
        $response->assertSessionHas('success', 'Your account has been deleted successfully');

        $this->assertGuest();
    }

    public function test_successful_self_delete_removes_user_from_database(): void
    {
        $user = $this->regularUser();
        $userId = $user->id;

        $response = $this->actingAs($user)
            ->delete(route('users.profile.destroy'));

        $response->assertRedirect(route('home'));
        $response->assertSessionHas('success', 'Your account has been deleted successfully');

        $this->assertDatabaseMissing('users', ['id' => $userId]);
    }

    public function test_successful_self_delete_redirects_to_home(): void
    {
        $user = $this->regularUser();

        $response = $this->actingAs($user)
            ->delete(route('users.profile.destroy'));

        $response->assertRedirect(route('home'));
    }

    public function test_successful_self_delete_flashes_success_message(): void
    {
        $user = $this->regularUser();

        $response = $this->actingAs($user)
            ->delete(route('users.profile.destroy'));

        $response->assertSessionHas('success', 'Your account has been deleted successfully');
    }

    public function test_successful_self_delete_logs_user_out(): void
    {
        $user = $this->regularUser();

        $this->actingAs($user)
            ->delete(route('users.profile.destroy'));

        $this->assertGuest();
    }
}
