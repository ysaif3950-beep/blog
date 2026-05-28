<?php

namespace Tests\Feature\Api\V1\Auth;

use App\Events\ResetPasswordEvent;
use App\Listeners\SendResetPasswordEmail;
use App\Models\User;
use Illuminate\Auth\Events\PasswordResetLinkSent;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Feature\Api\V1\ApiTestCase;

class ForgetPasswordTest extends ApiTestCase
{
    private const RESET_LINK_RESPONSE_MESSAGE = 'If your email exists, a reset link has been sent';

    public function test_user_can_request_password_reset(): void
    {
        Notification::fake();

        $user = User::factory()->create([
            'email' => 'user@example.com',
        ]);

        Event::fake([PasswordResetLinkSent::class]);
        Event::assertListening(ResetPasswordEvent::class, SendResetPasswordEmail::class);

        $response = $this->apiPost('/auth/forgot-password', [
            'email' => ' '.strtoupper($user->email).' ',
        ]);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', self::RESET_LINK_RESPONSE_MESSAGE)
            ->assertJsonMissingPath('data');

        $this->assertDatabaseHas($this->passwordResetTable(), [
            'email' => $user->email,
        ]);

        Notification::assertSentTo($user, ResetPassword::class, function (ResetPassword $notification) use ($user): bool {
            $resetEmail = $notification->toMail($user);

            return $resetEmail->actionUrl === route('password.reset', [
                'token' => $notification->token,
                'email' => $user->email,
            ]);
        });
        Event::assertDispatched(PasswordResetLinkSent::class, fn (PasswordResetLinkSent $event): bool => $event->user->is($user));
    }

    public function test_forgot_password_does_not_reveal_whether_email_exists(): void
    {
        Notification::fake();

        $email = 'missing@example.com';

        $response = $this->apiPost('/auth/forgot-password', [
            'email' => $email,
        ]);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', self::RESET_LINK_RESPONSE_MESSAGE)
            ->assertJsonMissingPath('data');

        $this->assertDatabaseMissing($this->passwordResetTable(), [
            'email' => $email,
        ]);

        Notification::assertNothingSent();
    }

    #[DataProvider('invalidEmailPayloads')]
    public function test_forgot_password_rejects_invalid_payloads(array $payload, array $expectedErrors): void
    {
        Notification::fake();

        $this->apiPost('/auth/forgot-password', $payload)
            ->assertStatus(422)
            ->assertJsonValidationErrors($expectedErrors);

        $this->assertDatabaseCount($this->passwordResetTable(), 0);
        Notification::assertNothingSent();
    }

    /**
     * @return array<string, array{0: array<string, mixed>, 1: list<string>}>
     */
    public static function invalidEmailPayloads(): array
    {
        return [
            'empty payload' => [
                [],
                ['email'],
            ],
            'email is null' => [
                ['email' => null],
                ['email'],
            ],
            'email is empty' => [
                ['email' => ''],
                ['email'],
            ],
            'email contains only spaces' => [
                ['email' => '   '],
                ['email'],
            ],
            'email is invalid' => [
                ['email' => 'not-an-email'],
                ['email'],
            ],
            'email is not a string' => [
                ['email' => ['user@example.com']],
                ['email'],
            ],
            'email is too long' => [
                ['email' => str_repeat('a', 250).'@example.com'],
                ['email'],
            ],
        ];
    }

    private function passwordResetTable(): string
    {
        return (string) config('auth.passwords.users.table');
    }
}
