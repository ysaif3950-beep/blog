<?php

namespace Tests\Feature\Web\User;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class StoreTest extends TestCase
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

    protected function validPayload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'user',
        ], $overrides);
    }

    public function test_unauthenticated_user_is_redirected_to_login(): void
    {
        $response = $this->post(route('users.store'), $this->validPayload());

        $response->assertRedirect(route('login'));
    }

    public function test_regular_user_is_forbidden_from_storing(): void
    {
        $response = $this->actingAs($this->regularUser())
            ->post(route('users.store'), $this->validPayload());

        $response->assertForbidden();
    }

    public function test_admin_can_store_user_with_valid_data(): void
    {
        $payload = $this->validPayload();

        $response = $this->actingAs($this->admin())
            ->post(route('users.store'), $payload);

        $response->assertRedirect(route('users.index'));

        $this->assertDatabaseHas('users', [
            'email' => $payload['email'],
            'name' => $payload['name'],
            'role' => $payload['role'],
        ]);
    }

    public function test_stored_user_password_is_hashed(): void
    {
        $plainPassword = 'password123';

        $this->actingAs($this->admin())
            ->post(route('users.store'), $this->validPayload([
                'password' => $plainPassword,
                'password_confirmation' => $plainPassword,
            ]));

        $user = User::where('email', 'john@example.com')->first();

        $this->assertNotNull($user);
        $this->assertTrue(Hash::check($plainPassword, $user->password));
    }

    public function test_admin_is_redirected_to_users_index_with_success_message(): void
    {
        $response = $this->actingAs($this->admin())
            ->post(route('users.store'), $this->validPayload());

        $response->assertRedirect(route('users.index'));
        $response->assertSessionHas('success', 'User created successfully.');
    }

    public function test_store_validation_fails_when_required_fields_are_missing(): void
    {
        $response = $this->actingAs($this->admin())
            ->post(route('users.store'), []);

        $response->assertRedirect();
        $response->assertSessionHasErrors(['name', 'email', 'password', 'role']);
    }

    public function test_store_validation_fails_when_email_is_already_taken(): void
    {
        User::factory()->create(['email' => 'taken@example.com']);

        $response = $this->actingAs($this->admin())
            ->post(route('users.store'), $this->validPayload([
                'email' => 'taken@example.com',
            ]));

        $response->assertRedirect();
        $response->assertSessionHasErrors(['email']);
    }

    public function test_store_validation_fails_when_password_confirmation_does_not_match(): void
    {
        $response = $this->actingAs($this->admin())
            ->post(route('users.store'), $this->validPayload([
                'password_confirmation' => 'wrong-password',
            ]));

        $response->assertRedirect();
        $response->assertSessionHasErrors(['password']);
    }

    public function test_store_validation_fails_when_role_is_invalid(): void
    {
        $response = $this->actingAs($this->admin())
            ->post(route('users.store'), $this->validPayload([
                'role' => 'superadmin',
            ]));

        $response->assertRedirect();
        $response->assertSessionHasErrors(['role']);
    }

    public function test_admin_can_upload_profile_image(): void
    {
        Storage::fake('public');

        $image = UploadedFile::fake()->image('avatar.jpg');

        $response = $this->actingAs($this->admin())
            ->post(route('users.store'), $this->validPayload([
                'profile_image' => $image,
            ]));

        $response->assertRedirect(route('users.index'));

        $user = User::where('email', 'john@example.com')->first();
        $this->assertNotNull($user->profile_image);
        $this->assertStringStartsWith('profiles/', $user->profile_image);
        Storage::disk('public')->assertExists($user->profile_image);
    }
}
