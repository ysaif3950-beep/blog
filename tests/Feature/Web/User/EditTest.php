<?php

namespace Tests\Feature\Web\User;

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

    public function test_guest_is_redirected_to_login_when_visiting_users_edit(): void
    {
        $user = $this->regularUser();

        $response = $this->get(route('users.edit', $user));

        $response->assertRedirect(route('login'));
    }

    public function test_regular_user_can_edit_themselves(): void
    {
        $user = $this->regularUser();

        $response = $this->actingAs($user)->get(route('users.edit', $user));

        $response->assertOk();
    }

    public function test_regular_user_is_forbidden_from_editing_another_user(): void
    {
        $user = $this->regularUser();
        $anotherUser = $this->regularUser();

        $response = $this->actingAs($user)->get(route('users.edit', $anotherUser));

        $response->assertForbidden();
    }

    public function test_admin_can_edit_another_user(): void
    {
        $admin = $this->admin();
        $user = $this->regularUser();

        $response = $this->actingAs($admin)->get(route('users.edit', $user));

        $response->assertOk();
    }

    public function test_edit_response_uses_users_edit_profile_view(): void
    {
        $user = $this->regularUser();

        $response = $this->actingAs($user)->get(route('users.edit', $user));

        $response->assertViewIs('users.edit-profile');
    }

    public function test_edit_view_receives_the_target_user(): void
    {
        $user = $this->regularUser();

        $response = $this->actingAs($user)->get(route('users.edit', $user));

        $response->assertViewHas('user', $user);
    }

    public function test_edit_view_receives_correct_form_configuration(): void
    {
        $targetUser = $this->regularUser();

        $response = $this->actingAs($targetUser)->get(route('users.edit', $targetUser));

        $response->assertViewHas([
            'formAction' => route('users.update', $targetUser),
            'cancelUrl' => route('users.index'),
            'pageTitle' => 'Edit Profile',
            'pageDescription' => 'Update profile details, photo, and access level',
            'submitLabel' => 'Save Changes',
            'showRoleField' => true,
        ]);
    }
}
