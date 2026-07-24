<?php

namespace Tests\Feature\Web\User;

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

    public function test_guest_is_redirected_to_login_when_deleting_a_user(): void
    {
        $user = $this->regularUser();

        $response = $this->delete(route('users.destroy', $user));

        $response->assertRedirect(route('login'));
    }

    public function test_regular_user_is_forbidden_from_deleting_another_user(): void
    {
        $user = $this->regularUser();
        $anotherUser = $this->regularUser();

        $response = $this->actingAs($user)
            ->delete(route('users.destroy', $anotherUser));

        $response->assertForbidden();

        $this->assertDatabaseHas('users', [
            'id' => $anotherUser->id,
        ]);
    }

    public function test_regular_user_can_delete_themselves(): void
    {
        $user = $this->regularUser();

        $response = $this->actingAs($user)
            ->delete(route('users.destroy', $user));

        $response->assertRedirect(route('users.index'));
        $response->assertSessionHas('success', 'User deleted successfully.');

        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }

    public function test_admin_can_delete_another_user(): void
    {
        $admin = $this->admin();
        $user = $this->regularUser();

        $response = $this->actingAs($admin)
            ->delete(route('users.destroy', $user));

        $response->assertRedirect(route('users.index'));
        $response->assertSessionHas('success', 'User deleted successfully.');

        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }

    public function test_successful_delete_redirects_to_users_index(): void
    {
        $user = $this->regularUser();

        $response = $this->actingAs($user)
            ->delete(route('users.destroy', $user));

        $response->assertRedirect(route('users.index'));
    }

    public function test_successful_delete_flashes_success_message(): void
    {
        $user = $this->regularUser();

        $response = $this->actingAs($user)
            ->delete(route('users.destroy', $user));

        $response->assertSessionHas('success', 'User deleted successfully.');
    }

    public function test_successful_delete_removes_user_from_database(): void
    {
        $user = $this->regularUser();

        $this->actingAs($user)
            ->delete(route('users.destroy', $user));

        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }
}
