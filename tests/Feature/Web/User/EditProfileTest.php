<?php

namespace Tests\Feature\Web\User;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EditProfileTest extends TestCase
{
    use RefreshDatabase;

    protected function createUser(): User
    {
        return User::factory()->create(['role' => 'user']);
    }

    protected function createAdmin(): User
    {
        return User::factory()->create(['role' => 'admin']);
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $response = $this->get(route('users.profile.edit'));

        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_access_edit_profile_page(): void
    {
        $user = $this->createUser();

        $response = $this->actingAs($user)->get(route('users.profile.edit'));

        $response->assertOk();
    }

    public function test_edit_profile_uses_edit_profile_view(): void
    {
        $user = $this->createUser();

        $response = $this->actingAs($user)->get(route('users.profile.edit'));

        $response->assertViewIs('users.edit-profile');
    }

    public function test_edit_profile_view_receives_the_user(): void
    {
        $user = $this->createUser();

        $response = $this->actingAs($user)->get(route('users.profile.edit'));

        $response->assertViewHas('user');
        $this->assertTrue($response->viewData('user')->is($user));
    }

    public function test_edit_profile_view_has_correct_form_configuration(): void
    {
        $user = $this->createUser();

        $response = $this->actingAs($user)->get(route('users.profile.edit'));

        $response->assertViewHas([
            'formAction' => route('users.profile.update'),
            'cancelUrl' => route('users.profile'),
            'pageTitle' => 'Edit Profile',
            'pageDescription' => 'Update your profile details and photo',
            'submitLabel' => 'Save Profile',
        ]);
    }

    public function test_edit_profile_does_not_show_role_field(): void
    {
        $user = $this->createUser();

        $response = $this->actingAs($user)->get(route('users.profile.edit'));

        $response->assertViewHas('showRoleField', false);
    }

    public function test_admin_can_access_edit_profile_page(): void
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin)->get(route('users.profile.edit'));

        $response->assertOk();
        $response->assertViewIs('users.edit-profile');
    }

    public function test_edit_profile_view_has_correct_form_action_for_admin(): void
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin)->get(route('users.profile.edit'));

        $response->assertViewHas('formAction', route('users.profile.update'));
    }

    public function test_edit_profile_view_has_correct_cancel_url(): void
    {
        $user = $this->createUser();

        $response = $this->actingAs($user)->get(route('users.profile.edit'));

        $response->assertViewHas('cancelUrl', route('users.profile'));
    }

    public function test_edit_profile_view_has_correct_page_title(): void
    {
        $user = $this->createUser();

        $response = $this->actingAs($user)->get(route('users.profile.edit'));

        $response->assertViewHas('pageTitle', 'Edit Profile');
    }

    public function test_edit_profile_view_has_correct_page_description(): void
    {
        $user = $this->createUser();

        $response = $this->actingAs($user)->get(route('users.profile.edit'));

        $response->assertViewHas('pageDescription', 'Update your profile details and photo');
    }

    public function test_edit_profile_view_has_correct_submit_label(): void
    {
        $user = $this->createUser();

        $response = $this->actingAs($user)->get(route('users.profile.edit'));

        $response->assertViewHas('submitLabel', 'Save Profile');
    }
}
