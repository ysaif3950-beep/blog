<?php

namespace Tests\Feature\Api\V1\Auth;

use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Tests\Feature\Api\V1\ApiTestCase;

class RegisterTest extends ApiTestCase
{
    /**
     * دالة مساعدة لتوفير بيانات تسجيل صحيحة قابلة للتعديل
     */
    protected function validRegistrationData(array $overrides = []): array
    {
        return array_merge([
            'name' => 'messi',
            'email' => 'messi@gmail.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ], $overrides);
    }

    public function test_user_can_register_with_valid_data(): void
    {
        $data = $this->validRegistrationData();

        $response = $this->apiPost('/auth/register', $data);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'token',
                    'user' => ['id', 'name', 'email', 'role'],
                ],
            ])
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.user.name', $data['name'])
            ->assertJsonPath('data.user.email', $data['email'])
            ->assertJsonPath('data.user.role', 'user')
            ->assertJsonMissingPath('data.user.password')
            ->assertJsonMissingPath('data.user.remember_token');

        $this->assertDatabaseHas('users', [
            'email' => $data['email'],
            'name' => $data['name'],
            'role' => 'user',
        ]);

        $user = User::where('email', $data['email'])->first();

        $this->assertIsString($response->json('data.token'));
        $this->assertNotSame('', $response->json('data.token'));
        $this->assertTrue(Hash::check($data['password'], $user->password));
    }

    public function test_it_dispatches_registered_event(): void
    {
        Event::fake();

        $this->apiPost('/auth/register', $this->validRegistrationData());

       Event::assertDispatched(Registered::class, function ($event) {
    return $event->user->email === 'messi@gmail.com';
});
    }

    public function test_register_ignores_unauthorized_fields_like_role(): void
    {
        // محاولة إرسال حقل الإدارة لاختبار ثغرة Mass Assignment
        $data = $this->validRegistrationData([
            'role' => 'admin',
            'is_admin' => true,
        ]);

        $response = $this->apiPost('/auth/register', $data);

        $response->assertStatus(201); // يجب أن ينجح التسجيل عادي

        // نتأكد أن المستخدم لم يأخذ الصلاحية الملقمة
        $this->assertDatabaseMissing('users', [
            'email' => $data['email'],
            'role' => 'admin',
        ]);
        $this->assertDatabaseHas('users', [
            'email' => $data['email'],
            'role' => 'user',
        ]);
    }

    public function test_register_fails_and_returns_all_required_errors_with_empty_payload(): void
    {
        $response = $this->apiPost('/auth/register', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'email', 'password']);
    }

    // ==========================================
    // Name Validations
    // ==========================================

    public function test_register_requires_name(): void
    {
        $data = $this->validRegistrationData(['name' => '']);

        $response = $this->apiPost('/auth/register', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }

    public function test_register_rejects_name_with_only_spaces(): void
    {
        $data = $this->validRegistrationData(['name' => '   ']);

        $response = $this->apiPost('/auth/register', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }

    public function test_register_requires_name_to_be_max_255_characters(): void
    {
        $data = $this->validRegistrationData([
            'name' => str_repeat('a', 256),
        ]);

        $response = $this->apiPost('/auth/register', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }

    public function test_register_rejects_name_if_not_string(): void
    {
        $data = $this->validRegistrationData(['name' => ['invalid array']]);

        $response = $this->apiPost('/auth/register', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }

    public function test_register_trims_whitespace_from_name_and_email(): void
    {
        $data = $this->validRegistrationData([
            'name' => '  Lionel Messi  ',
            'email' => '  lionel@gmail.com  ',
        ]);

        $response = $this->apiPost('/auth/register', $data);

        $response->assertStatus(201);

        $this->assertDatabaseHas('users', [
            'name' => 'Lionel Messi',
            'email' => 'lionel@gmail.com',
        ]);
    }

    // ==========================================
    // Email Validations
    // ==========================================

    public function test_register_requires_email(): void
    {
        $data = $this->validRegistrationData(['email' => '']);

        $response = $this->apiPost('/auth/register', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_register_requires_valid_email(): void
    {
        $data = $this->validRegistrationData(['email' => 'invalid-email']);

        $response = $this->apiPost('/auth/register', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_register_requires_email_to_be_max_255_characters(): void
    {
        $data = $this->validRegistrationData([
            'email' => str_repeat('a', 255).'@gmail.com',
        ]);

        $response = $this->apiPost('/auth/register', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_register_requires_unique_email(): void
    {
        User::factory()->create([
            'email' => 'messi@gmail.com',
        ]);

        $data = $this->validRegistrationData([
            'email' => 'messi@gmail.com',
        ]);

        $response = $this->apiPost('/auth/register', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_register_rejects_email_if_not_string(): void
    {
        $data = $this->validRegistrationData(['email' => ['invalid array']]);

        $response = $this->apiPost('/auth/register', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_register_rejects_duplicate_email_with_different_case(): void
    {
        User::factory()->create([
            'email' => 'messi@gmail.com',
        ]);

        $data = $this->validRegistrationData([
            'email' => 'MESSI@GMAIL.COM',
        ]);

        $response = $this->apiPost('/auth/register', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    // ==========================================
    // Password Validations
    // ==========================================

    public function test_register_requires_password(): void
    {
        $data = $this->validRegistrationData();
        unset($data['password']); // إزالة الباسورد كلياً لاختبار الـ required

        $response = $this->apiPost('/auth/register', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    }

    public function test_register_requires_password_to_be_at_least_8_characters(): void
    {
        $data = $this->validRegistrationData([
            'password' => 'short',
            'password_confirmation' => 'short',
        ]);

        $response = $this->apiPost('/auth/register', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    }

    public function test_register_requires_password_confirmation_to_match(): void
    {
        $data = $this->validRegistrationData([
            'password' => 'Password123!',
            'password_confirmation' => 'DifferentPassword!',
        ]);

        $response = $this->apiPost('/auth/register', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    }

    public function test_register_requires_password_to_be_max_255_characters(): void
    {
        $data = $this->validRegistrationData([
            'password' => str_repeat('a', 256),
            'password_confirmation' => str_repeat('a', 256),
        ]);

        $response = $this->apiPost('/auth/register', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    }
}
