<?php

namespace Tests\Feature\Web\User;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
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

    protected function validPayload(User $user, array $overrides = []): array
    {
        return array_merge([
            'name' => 'Updated Name',
            'email' => $user->email,
            'password' => '',
            'password_confirmation' => '',
            'role' => $user->role,
        ], $overrides);
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $user = $this->regularUser();

        $response = $this->put(route('users.update', $user), $this->validPayload($user));

        $response->assertRedirect(route('login'));
    }

    public function test_regular_user_can_update_themselves(): void
    {
        $user = $this->regularUser();

        $response = $this->actingAs($user)
            ->put(route('users.update', $user), $this->validPayload($user, [
                'name' => 'New Name',
            ]));

        $response->assertRedirect(route('users.index'));
        $response->assertSessionHas('success', 'User updated successfully!');

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'New Name',
            'role' => 'user',
        ]);
    }

    public function test_regular_user_is_forbidden_from_updating_another_user(): void
    {
        $user = $this->regularUser();
        $anotherUser = $this->regularUser();

        $response = $this->actingAs($user)
            ->put(route('users.update', $anotherUser), $this->validPayload($anotherUser));

        $response->assertForbidden();
    }

    public function test_admin_can_update_any_user(): void
    {
        $admin = $this->admin();
        $user = $this->regularUser();

        $response = $this->actingAs($admin)
            ->put(route('users.update', $user), $this->validPayload($user, [
                'name' => 'Admin Changed It',
            ]));

        $response->assertRedirect(route('users.index'));
        $response->assertSessionHas('success', 'User updated successfully!');

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Admin Changed It',
        ]);
    }

    public function test_update_redirects_to_users_index_on_success(): void
    {
        $user = $this->regularUser();

        $response = $this->actingAs($user)
            ->put(route('users.update', $user), $this->validPayload($user));

        $response->assertRedirect(route('users.index'));
    }

    public function test_update_sets_success_flash_message(): void
    {
        $user = $this->regularUser();

        $response = $this->actingAs($user)
            ->put(route('users.update', $user), $this->validPayload($user));

        $response->assertSessionHas('success', 'User updated successfully!');
    }

    public function test_update_validation_fails_when_name_is_missing(): void
    {
        $user = $this->regularUser();

        $response = $this->actingAs($user)
            ->put(route('users.update', $user), $this->validPayload($user, [
                'name' => '',
            ]));

        $response->assertRedirect();
        $response->assertSessionHasErrors(['name']);
    }

    public function test_update_validation_fails_when_name_is_too_short(): void
    {
        $user = $this->regularUser();

        $response = $this->actingAs($user)
            ->put(route('users.update', $user), $this->validPayload($user, [
                'name' => 'ab',
            ]));

        $response->assertRedirect();
        $response->assertSessionHasErrors(['name']);
    }

    public function test_update_validation_fails_when_email_is_missing(): void
    {
        $user = $this->regularUser();

        $response = $this->actingAs($user)
            ->put(route('users.update', $user), $this->validPayload($user, [
                'email' => '',
            ]));

        $response->assertRedirect();
        $response->assertSessionHasErrors(['email']);
    }

    public function test_update_validation_fails_when_email_is_invalid(): void
    {
        $user = $this->regularUser();

        $response = $this->actingAs($user)
            ->put(route('users.update', $user), $this->validPayload($user, [
                'email' => 'not-an-email',
            ]));

        $response->assertRedirect();
        $response->assertSessionHasErrors(['email']);
    }

    public function test_update_allows_same_email_for_current_user(): void
    {
        $user = $this->regularUser();

        $response = $this->actingAs($user)
            ->put(route('users.update', $user), $this->validPayload($user, [
                'email' => $user->email,
            ]));

        $response->assertRedirect(route('users.index'));
        $response->assertSessionHasNoErrors();
    }

    public function test_update_validation_fails_when_email_is_taken_by_another_user(): void
    {
        $user = $this->regularUser();
        $takenEmail = $this->regularUser()->email;

        $response = $this->actingAs($user)
            ->put(route('users.update', $user), $this->validPayload($user, [
                'email' => $takenEmail,
            ]));

        $response->assertRedirect();
        $response->assertSessionHasErrors(['email']);
    }

    public function test_update_validation_fails_when_role_is_missing(): void
    {
        $user = $this->regularUser();

        $response = $this->actingAs($user)
            ->put(route('users.update', $user), $this->validPayload($user, [
                'role' => '',
            ]));

        $response->assertRedirect();
        $response->assertSessionHasErrors(['role']);
    }

    public function test_update_validation_fails_when_role_is_invalid(): void
    {
        $user = $this->regularUser();

        $response = $this->actingAs($user)
            ->put(route('users.update', $user), $this->validPayload($user, [
                'role' => 'superadmin',
            ]));

        $response->assertRedirect();
        $response->assertSessionHasErrors(['role']);
    }

    public function test_update_validation_fails_when_password_is_too_short(): void
    {
        $user = $this->regularUser();

        $response = $this->actingAs($user)
            ->put(route('users.update', $user), $this->validPayload($user, [
                'password' => 'short',
                'password_confirmation' => 'short',
            ]));

        $response->assertRedirect();
        $response->assertSessionHasErrors(['password']);
    }

    public function test_update_validation_fails_when_password_confirmation_does_not_match(): void
    {
        $user = $this->regularUser();

        $response = $this->actingAs($user)
            ->put(route('users.update', $user), $this->validPayload($user, [
                'password' => 'password123',
                'password_confirmation' => 'different',
            ]));

        $response->assertRedirect();
        $response->assertSessionHasErrors(['password']);
    }

    public function test_update_hashes_password_when_provided(): void
    {
        $user = $this->regularUser();
        $newPassword = 'newpassword123';

        $this->actingAs($user)
            ->put(route('users.update', $user), $this->validPayload($user, [
                'password' => $newPassword,
                'password_confirmation' => $newPassword,
            ]));

        $user->refresh();
        $this->assertTrue(Hash::check($newPassword, $user->password));
    }

    public function test_update_does_not_change_password_when_omitted(): void
    {
        $user = $this->regularUser();
        $originalPassword = $user->password;

        $this->actingAs($user)
            ->put(route('users.update', $user), $this->validPayload($user, [
                'password' => '',
                'password_confirmation' => '',
            ]));

        $user->refresh();
        $this->assertEquals($originalPassword, $user->password);
    }

    public function test_admin_can_change_role(): void
    {
        $admin = $this->admin();
        $user = $this->regularUser();

        $response = $this->actingAs($admin)
            ->put(route('users.update', $user), $this->validPayload($user, [
                'role' => 'admin',
            ]));

        $response->assertRedirect(route('users.index'));

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'role' => 'admin',
        ]);
    }

    public function test_regular_user_can_upload_profile_image(): void
    {
        Storage::fake('public');
        $user = $this->regularUser();
        $image = UploadedFile::fake()->image('avatar.jpg');

        $response = $this->actingAs($user)
            ->put(route('users.update', $user), $this->validPayload($user, [
                'profile_image' => $image,
            ]));

        $response->assertRedirect(route('users.index'));

        $user->refresh();
        $this->assertNotNull($user->profile_image);
        $this->assertStringStartsWith('profiles/', $user->profile_image);
        Storage::disk('public')->assertExists($user->profile_image);
    }

    public function test_regular_user_cannot_promote_themselves_to_admin(): void
    {
        $user = $this->regularUser();

        $response = $this->actingAs($user)
            ->put(route('users.update', $user), $this->validPayload($user, [
                'role' => 'admin',
            ]));

        $response->assertRedirect(route('users.index'));

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'role' => 'user',
        ]);
    }

    public function test_update_persists_email_change(): void
    {
        $user = $this->regularUser();
        $newEmail = 'newemail@example.com';

        $this->actingAs($user)
            ->put(route('users.update', $user), $this->validPayload($user, [
                'email' => $newEmail,
            ]));

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'email' => $newEmail,
        ]);
    }

    public function test_update_deletes_old_profile_image_when_new_image_is_uploaded(): void
    {
        Storage::fake('public');

        $user = $this->regularUser();
        $oldImagePath = UploadedFile::fake()->image('old.jpg')->store('profiles', 'public');
        $user->update(['profile_image' => $oldImagePath]);
        $user->refresh();

        $this->assertTrue(Storage::disk('public')->exists($oldImagePath));

        $newImage = UploadedFile::fake()->image('new.jpg');

        $response = $this->actingAs($user)
            ->put(route('users.update', $user), $this->validPayload($user, [
                'profile_image' => $newImage,
            ]));

        $response->assertRedirect(route('users.index'));

        $user->refresh();
        $this->assertNotNull($user->profile_image);
        $this->assertNotEquals($oldImagePath, $user->profile_image);
        $this->assertFalse(Storage::disk('public')->exists($oldImagePath));
        Storage::disk('public')->assertExists($user->profile_image);
    }

    public function test_update_validation_fails_when_profile_image_is_invalid_type(): void
    {
        $user = $this->regularUser();

        $response = $this->actingAs($user)
            ->put(route('users.update', $user), $this->validPayload($user, [
                'profile_image' => UploadedFile::fake()->create('document.pdf', 100),
            ]));

        $response->assertRedirect();
        $response->assertSessionHasErrors(['profile_image']);
    }
}
