<?php

namespace Tests\Feature\Api\V1\Auth;

use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Feature\Api\V1\ApiTestCase;

class ResetPasswordTest extends ApiTestCase
{
    private const OLD_PASSWORD = 'OldPassword123!';

    private const NEW_PASSWORD = 'NewPassword123!';

    public function test_user_can_reset_password_with_valid_token(): void
    {
        Event::fake([PasswordReset::class]);

        $user = User::factory()->create([
            'email' => 'user@example.com',
            'password' => Hash::make(self::OLD_PASSWORD),
            'remember_token' => 'old-remember-token',
        ]);
        $token = Password::createToken($user);

        $response = $this->apiPost('/auth/reset-password', $this->validResetPasswordData($user->email, $token));

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Password reset successfully')
            ->assertJsonMissingPath('data');

        $freshUser = $user->fresh();

        $this->assertTrue(Hash::check(self::NEW_PASSWORD, $freshUser->password));
        $this->assertFalse(Hash::check(self::OLD_PASSWORD, $freshUser->password));
        $this->assertNotSame('old-remember-token', $freshUser->remember_token);
        $this->assertDatabaseMissing($this->passwordResetTable(), [
            'email' => $user->email,
        ]);

        Event::assertDispatched(PasswordReset::class, fn (PasswordReset $event): bool => $event->user->is($user));
    }

    public function test_reset_password_fails_with_invalid_token(): void
    {
        $user = User::factory()->create([
            'email' => 'user@example.com',
            'password' => Hash::make(self::OLD_PASSWORD),
        ]);
        Password::createToken($user);

        $this->apiPost('/auth/reset-password', $this->validResetPasswordData($user->email, 'invalid-token'))
            ->assertStatus(400)
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'Invalid token or email');

        $this->assertTrue(Hash::check(self::OLD_PASSWORD, $user->fresh()->password));
        $this->assertDatabaseHas($this->passwordResetTable(), [
            'email' => $user->email,
        ]);
    }

    public function test_reset_password_fails_with_wrong_email(): void
    {
        $user = User::factory()->create([
            'email' => 'user@example.com',
            'password' => Hash::make(self::OLD_PASSWORD),
        ]);
        $token = Password::createToken($user);

        $this->apiPost('/auth/reset-password', $this->validResetPasswordData('missing@example.com', $token))
            ->assertStatus(400)
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'Invalid token or email');

        $this->assertTrue(Hash::check(self::OLD_PASSWORD, $user->fresh()->password));
        $this->assertDatabaseHas($this->passwordResetTable(), [
            'email' => $user->email,
        ]);
    }

    #[DataProvider('invalidResetPasswordPayloads')]
    public function test_reset_password_rejects_invalid_payloads(array $payload, array $expectedErrors): void
    {
        $this->apiPost('/auth/reset-password', $payload)
            ->assertStatus(422)
            ->assertJsonValidationErrors($expectedErrors);

        $this->assertDatabaseCount($this->passwordResetTable(), 0);
    }

    /**
     * @return array<string, array{0: array<string, mixed>, 1: list<string>}>
     */
    public static function invalidResetPasswordPayloads(): array
    {
        return [
            'empty payload' => [
                [],
                ['token', 'email', 'password'],
            ],
            'token is null' => [
                self::validResetPasswordPayload(['token' => null]),
                ['token'],
            ],
            'token is empty' => [
                self::validResetPasswordPayload(['token' => '']),
                ['token'],
            ],
            'email is null' => [
                self::validResetPasswordPayload(['email' => null]),
                ['email'],
            ],
            'email is empty' => [
                self::validResetPasswordPayload(['email' => '']),
                ['email'],
            ],
            'email contains only spaces' => [
                self::validResetPasswordPayload(['email' => '   ']),
                ['email'],
            ],
            'email is invalid' => [
                self::validResetPasswordPayload(['email' => 'not-an-email']),
                ['email'],
            ],
            'password is missing' => [
                self::validResetPasswordPayload([], ['password']),
                ['password'],
            ],
            'password is null' => [
                self::validResetPasswordPayload(['password' => null]),
                ['password'],
            ],
            'password is empty' => [
                self::validResetPasswordPayload(['password' => '']),
                ['password'],
            ],
            'password is too short' => [
                self::validResetPasswordPayload([
                    'password' => 'short',
                    'password_confirmation' => 'short',
                ]),
                ['password'],
            ],
            'password confirmation is missing' => [
                self::validResetPasswordPayload([], ['password_confirmation']),
                ['password'],
            ],
            'password confirmation does not match' => [
                self::validResetPasswordPayload([
                    'password_confirmation' => 'DifferentPassword123!',
                ]),
                ['password'],
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function validResetPasswordData(string $email, string $token, array $overrides = []): array
    {
        return array_merge([
            'token' => $token,
            'email' => $email,
            'password' => self::NEW_PASSWORD,
            'password_confirmation' => self::NEW_PASSWORD,
        ], $overrides);
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @param  list<string>  $missingKeys
     * @return array<string, mixed>
     */
    private static function validResetPasswordPayload(array $overrides = [], array $missingKeys = []): array
    {
        $payload = array_merge([
            'token' => 'reset-token',
            'email' => 'user@example.com',
            'password' => self::NEW_PASSWORD,
            'password_confirmation' => self::NEW_PASSWORD,
        ], $overrides);

        foreach ($missingKeys as $missingKey) {
            unset($payload[$missingKey]);
        }

        return $payload;
    }

    private function passwordResetTable(): string
    {
        return (string) config('auth.passwords.users.table');
    }
}
