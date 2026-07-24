<?php

namespace Tests\Feature\Web\User;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class UpdateProfileTest extends TestCase
{
    use RefreshDatabase;

    protected function createUser(): User
    {
        return User::factory()->create([
            'role' => 'user',
            'password' => Hash::make('oldpassword123'),
        ]);
    }

    protected function createAdmin(): User
    {
        return User::factory()->create([
            'role' => 'admin',
            'password' => Hash::make('oldpassword123'),
        ]);
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $response = $this->put(route('users.profile.update'));

        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_update_their_profile(): void
    {
        $user = $this->createUser();

        $response = $this->actingAs($user)->put(route('users.profile.update'), [
            'name' => 'Updated Name',
            'email' => 'updated@example.com',
        ]);

        $response->assertRedirect(route('users.profile'));
        $response->assertSessionHas('success', 'Profile updated successfully.');
    }

    public function test_update_profile_changes_name(): void
    {
        $user = $this->createUser();

        $this->actingAs($user)->put(route('users.profile.update'), [
            'name' => 'New Name',
            'email' => $user->email,
        ]);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'New Name',
        ]);
    }

    public function test_update_profile_changes_email(): void
    {
        $user = $this->createUser();

        $this->actingAs($user)->put(route('users.profile.update'), [
            'name' => $user->name,
            'email' => 'newemail@example.com',
        ]);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'email' => 'newemail@example.com',
        ]);
    }

    public function test_update_profile_hashes_new_password(): void
    {
        $user = $this->createUser();

        $this->actingAs($user)->put(route('users.profile.update'), [
            'name' => $user->name,
            'email' => $user->email,
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);

        $user->refresh();
        $this->assertTrue(Hash::check('newpassword123', $user->password));
    }

    public function test_update_profile_does_not_change_password_if_not_provided(): void
    {
        $user = $this->createUser();
        $originalPassword = $user->password;

        $this->actingAs($user)->put(route('users.profile.update'), [
            'name' => 'New Name',
            'email' => $user->email,
        ]);

        $user->refresh();
        $this->assertEquals($originalPassword, $user->password);
    }

    public function test_update_profile_with_image_stores_image(): void
    {
        Storage::fake('public');
        $user = $this->createUser();

        $file = UploadedFile::fake()->image('profile.jpg');

        $this->actingAs($user)->put(route('users.profile.update'), [
            'name' => $user->name,
            'email' => $user->email,
            'profile_image' => $file,
        ]);

        $user->refresh();
        $this->assertNotNull($user->profile_image);
        Storage::disk('public')->assertExists($user->profile_image);
    }

    public function test_update_profile_with_new_image_replaces_old_image(): void
    {
        Storage::fake('public');
        $user = $this->createUser();

        $oldFile = UploadedFile::fake()->image('old.jpg');
        $oldPath = $oldFile->store('profiles', 'public');
        $user->update(['profile_image' => $oldPath]);

        $newFile = UploadedFile::fake()->image('new.jpg');

        $this->actingAs($user)->put(route('users.profile.update'), [
            'name' => $user->name,
            'email' => $user->email,
            'profile_image' => $newFile,
        ]);

        $user->refresh();
        Storage::disk('public')->assertMissing($oldPath);
        Storage::disk('public')->assertExists($user->profile_image);
        $this->assertNotEquals($oldPath, $user->profile_image);
    }

    public function test_update_profile_redirects_to_profile_page_with_success_message(): void
    {
        $user = $this->createUser();

        $response = $this->actingAs($user)->put(route('users.profile.update'), [
            'name' => 'Updated Name',
            'email' => $user->email,
        ]);

        $response->assertRedirect(route('users.profile'));
        $response->assertSessionHas('success', 'Profile updated successfully.');
    }

    public function test_update_profile_validates_required_fields(): void
    {
        $user = $this->createUser();

        $response = $this->actingAs($user)->put(route('users.profile.update'), [
            'name' => '',
            'email' => '',
        ]);

        $response->assertSessionHasErrors(['name', 'email']);
    }

    public function test_update_profile_validates_email_format(): void
    {
        $user = $this->createUser();

        $response = $this->actingAs($user)->put(route('users.profile.update'), [
            'name' => 'Test User',
            'email' => 'invalid-email',
        ]);

        $response->assertSessionHasErrors(['email']);
    }

    public function test_update_profile_validates_password_confirmation(): void
    {
        $user = $this->createUser();

        $response = $this->actingAs($user)->put(route('users.profile.update'), [
            'name' => $user->name,
            'email' => $user->email,
            'password' => 'newpassword123',
            'password_confirmation' => 'differentpassword',
        ]);

        $response->assertSessionHasErrors(['password']);
    }

    public function test_update_profile_validates_password_min_length(): void
    {
        $user = $this->createUser();

        $response = $this->actingAs($user)->put(route('users.profile.update'), [
            'name' => $user->name,
            'email' => $user->email,
            'password' => 'short',
            'password_confirmation' => 'short',
        ]);

        $response->assertSessionHasErrors(['password']);
    }

    public function test_update_profile_validates_image_type(): void
    {
        $user = $this->createUser();

        $file = UploadedFile::fake()->create('document.pdf', 100);

        $response = $this->actingAs($user)->put(route('users.profile.update'), [
            'name' => $user->name,
            'email' => $user->email,
            'profile_image' => $file,
        ]);

        $response->assertSessionHasErrors(['profile_image']);
    }

    public function test_admin_can_update_their_profile(): void
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin)->put(route('users.profile.update'), [
            'name' => 'Admin Updated',
            'email' => $admin->email,
        ]);

        $response->assertRedirect(route('users.profile'));
        $response->assertSessionHas('success', 'Profile updated successfully.');

        $this->assertDatabaseHas('users', [
            'id' => $admin->id,
            'name' => 'Admin Updated',
        ]);
    }
}
