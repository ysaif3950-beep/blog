<?php

namespace Tests\Feature\Api\V1\Auth;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Feature\Api\V1\ApiTestCase;

class ChangePasswordTest extends ApiTestCase
{
    private const OLD_PASSWORD = 'OldPassword123!';

    private const NEW_PASSWORD = 'NewPassword123!';
    
    protected function validPasswordData(array $overrides = []): array
    {
        return array_merge([
            'current_password' => self::OLD_PASSWORD,
            'new_password' => self::NEW_PASSWORD,
            'new_password_confirmation' => self::NEW_PASSWORD,
        ], $overrides);
    }

    public function test_user_can_change_password_with_valid_data(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make(self::OLD_PASSWORD),
        ]);
        $currentToken = $user->createToken('auth-token');
        $otherToken = $user->createToken('other-device');

        $response = $this->apiPost('/auth/change-password', $this->validPasswordData(), [
            'Authorization' => 'Bearer '.$currentToken->plainTextToken,
        ]);

        $response->assertOk()
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'token',
                    'user' => ['id', 'name', 'email', 'role'],
                ],
            ])
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Password changed successfully')
            ->assertJsonPath('data.user.id', $user->id)
            ->assertJsonPath('data.user.email', $user->email)
            ->assertJsonMissingPath('data.user.password')
            ->assertJsonMissingPath('data.user.remember_token')
            ->assertJsonMissingPath('data.user.posts');

        $newToken = $response->json('data.token');

        $this->assertIsString($newToken);
        $this->assertNotSame('', $newToken);
        $this->assertNotSame($currentToken->plainTextToken, $newToken);
        $this->assertTrue(Hash::check(self::NEW_PASSWORD, $user->fresh()->password));
        $this->assertFalse(Hash::check(self::OLD_PASSWORD, $user->fresh()->password));

        $this->assertDatabaseMissing('personal_access_tokens', [
            'id' => $currentToken->accessToken->id,
        ]);
        $this->assertDatabaseMissing('personal_access_tokens', [
            'id' => $otherToken->accessToken->id,
        ]);
        $this->assertDatabaseCount('personal_access_tokens', 1);
        $this->assertDatabaseHas('personal_access_tokens', [
            'tokenable_type' => User::class,
            'tokenable_id' => $user->id,
            'name' => 'auth-token',
        ]);

        Auth::forgetGuards();

        $this->apiGet('/auth/user', [
            'Authorization' => 'Bearer '.$newToken,
        ])->assertOk()
            ->assertJsonPath('data.id', $user->id);

        Auth::forgetGuards();

        $this->apiGet('/auth/user', [
            'Authorization' => 'Bearer '.$currentToken->plainTextToken,
        ])->assertUnauthorized();
    }

    public function test_change_password_does_not_delete_other_users_tokens(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make(self::OLD_PASSWORD),
        ]);
        $otherUser = User::factory()->create();
        $currentToken = $user->createToken('auth-token');
        $otherUserToken = $otherUser->createToken('auth-token');

        $this->apiPost('/auth/change-password', $this->validPasswordData(), [
            'Authorization' => 'Bearer '.$currentToken->plainTextToken,
        ])->assertOk();

        $this->assertDatabaseMissing('personal_access_tokens', [
            'id' => $currentToken->accessToken->id,
        ]);
        $this->assertDatabaseHas('personal_access_tokens', [
            'id' => $otherUserToken->accessToken->id,
            'tokenable_type' => User::class,
            'tokenable_id' => $otherUser->id,
            'name' => 'auth-token',
        ]);

        Auth::forgetGuards();

        $this->apiGet('/auth/user', [
            'Authorization' => 'Bearer '.$otherUserToken->plainTextToken,
        ])->assertOk()
            ->assertJsonPath('data.id', $otherUser->id);
    }

    public function test_guest_cannot_change_password(): void
    {
        $this->apiPost('/auth/change-password', $this->validPasswordData())
            ->assertUnauthorized();

        $this->assertDatabaseCount('personal_access_tokens', 0);
    }

    public function test_change_password_with_invalid_token_returns_unauthorized(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make(self::OLD_PASSWORD),
        ]);
        $token = $user->createToken('auth-token');

        $this->apiPost('/auth/change-password', $this->validPasswordData(), [
            'Authorization' => 'Bearer invalid-token',
        ])->assertUnauthorized();

        $this->assertTrue(Hash::check(self::OLD_PASSWORD, $user->fresh()->password));
        $this->assertDatabaseCount('personal_access_tokens', 1);
        $this->assertDatabaseHas('personal_access_tokens', [
            'id' => $token->accessToken->id,
        ]);
    }

    public function test_change_password_with_expired_session_deletes_token_and_returns_unauthorized(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make(self::OLD_PASSWORD),
        ]);
        $token = $user->createToken('auth-token');
        $token->accessToken->forceFill([
            'last_used_at' => now()->subMinutes(config('sanctum.refresh_ttl', 30) + 1),
        ])->save();

        $this->apiPost('/auth/change-password', $this->validPasswordData(), [
            'Authorization' => 'Bearer '.$token->plainTextToken,
        ])->assertUnauthorized()
            ->assertJson([
                'success' => false,
                'message' => 'Session expired, please login again',
            ]);

        $this->assertTrue(Hash::check(self::OLD_PASSWORD, $user->fresh()->password));
        $this->assertDatabaseMissing('personal_access_tokens', [
            'id' => $token->accessToken->id,
        ]);
    }

    public function test_change_password_fails_with_wrong_current_password(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make(self::OLD_PASSWORD),
        ]);
        $token = $user->createToken('auth-token');

        $this->apiPost('/auth/change-password', $this->validPasswordData([
            'current_password' => 'WrongPassword123!',
        ]), [
            'Authorization' => 'Bearer '.$token->plainTextToken,
        ])->assertStatus(422)
            ->assertJsonValidationErrors(['current_password']);

        $this->assertTrue(Hash::check(self::OLD_PASSWORD, $user->fresh()->password));
        $this->assertDatabaseCount('personal_access_tokens', 1);
        $this->assertDatabaseHas('personal_access_tokens', [
            'id' => $token->accessToken->id,
        ]);
    }

    #[DataProvider('invalidPasswordPayloads')]
    public function test_change_password_rejects_invalid_payloads(array $payload, array $expectedErrors): void
    {
        $user = User::factory()->create([
            'password' => Hash::make(self::OLD_PASSWORD),
        ]);
        $token = $user->createToken('auth-token');

        $this->apiPost('/auth/change-password', $payload, [
            'Authorization' => 'Bearer '.$token->plainTextToken,
        ])->assertStatus(422)
            ->assertJsonValidationErrors($expectedErrors);

        $this->assertTrue(Hash::check(self::OLD_PASSWORD, $user->fresh()->password));
        $this->assertDatabaseCount('personal_access_tokens', 1);
        $this->assertDatabaseHas('personal_access_tokens', [
            'id' => $token->accessToken->id,
        ]);
    }

    public static function invalidPasswordPayloads(): array
    {
        return [
            'empty payload' => [
                [],
                ['current_password', 'new_password', 'new_password_confirmation'],
            ],
            'current password is null' => [
                [
                    'current_password' => null,
                    'new_password' => self::NEW_PASSWORD,
                    'new_password_confirmation' => self::NEW_PASSWORD,
                ],
                ['current_password'],
            ],
            'current password is empty' => [
                [
                    'current_password' => '',
                    'new_password' => self::NEW_PASSWORD,
                    'new_password_confirmation' => self::NEW_PASSWORD,
                ],
                ['current_password'],
            ],
            'current password contains only spaces' => [
                [
                    'current_password' => '   ',
                    'new_password' => self::NEW_PASSWORD,
                    'new_password_confirmation' => self::NEW_PASSWORD,
                ],
                ['current_password'],
            ],
            'current password is not a string' => [
                [
                    'current_password' => ['invalid'],
                    'new_password' => self::NEW_PASSWORD,
                    'new_password_confirmation' => self::NEW_PASSWORD,
                ],
                ['current_password'],
            ],
            'new password is missing' => [
                [
                    'current_password' => self::OLD_PASSWORD,
                    'new_password_confirmation' => self::NEW_PASSWORD,
                ],
                ['new_password'],
            ],
            'new password is null' => [
                [
                    'current_password' => self::OLD_PASSWORD,
                    'new_password' => null,
                    'new_password_confirmation' => self::NEW_PASSWORD,
                ],
                ['new_password'],
            ],
            'new password is empty' => [
                [
                    'current_password' => self::OLD_PASSWORD,
                    'new_password' => '',
                    'new_password_confirmation' => self::NEW_PASSWORD,
                ],
                ['new_password'],
            ],
            'new password contains only spaces' => [
                [
                    'current_password' => self::OLD_PASSWORD,
                    'new_password' => '        ',
                    'new_password_confirmation' => self::NEW_PASSWORD,
                ],
                ['new_password'],
            ],
            'new password is too short' => [
                [
                    'current_password' => self::OLD_PASSWORD,
                    'new_password' => 'short',
                    'new_password_confirmation' => 'short',
                ],
                ['new_password'],
            ],
            'new password is not a string' => [
                [
                    'current_password' => self::OLD_PASSWORD,
                    'new_password' => ['invalid'],
                    'new_password_confirmation' => self::NEW_PASSWORD,
                ],
                ['new_password'],
            ],
            'new password confirmation does not match' => [
                [
                    'current_password' => self::OLD_PASSWORD,
                    'new_password' => self::NEW_PASSWORD,
                    'new_password_confirmation' => 'DifferentPassword123!',
                ],
                ['new_password'],
            ],
            'new password confirmation is missing' => [
                [
                    'current_password' => self::OLD_PASSWORD,
                    'new_password' => self::NEW_PASSWORD,
                ],
                ['new_password', 'new_password_confirmation'],
            ],
            'new password confirmation is null' => [
                [
                    'current_password' => self::OLD_PASSWORD,
                    'new_password' => self::NEW_PASSWORD,
                    'new_password_confirmation' => null,
                ],
                ['new_password', 'new_password_confirmation'],
            ],
            'new password confirmation is not a string' => [
                [
                    'current_password' => self::OLD_PASSWORD,
                    'new_password' => self::NEW_PASSWORD,
                    'new_password_confirmation' => ['invalid'],
                ],
                ['new_password', 'new_password_confirmation'],
            ],
        ];
    }

    
}
