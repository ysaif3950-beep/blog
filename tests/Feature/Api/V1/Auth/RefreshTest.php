<?php

namespace Tests\Feature\Api\V1\Auth;

use App\Models\User;
use Tests\Feature\Api\V1\ApiTestCase;

class RefreshTest extends ApiTestCase
{
    public function test_refresh_token(): void
    {
        $user = User::factory()->create();
        $deviceName = substr(hash('sha256', 'Symfony'), 0, 32);
        $token = $user->createToken($deviceName)->plainTextToken;

        $response = $this->apiPost('/auth/refresh', [], [
            'Authorization' => 'Bearer '.$token,
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'token',
                    'expires_in',
                    'user' => [
                        'id',
                        'name',
                        'email',
                    ],
                ],
            ])
            ->assertJson([
                'data' => [
                    'expires_in' => config('sanctum.expiration', 60) * 60,
                ],
            ]);
    }

    public function test_refresh_with_mismatched_device_returns_invalid_device(): void
    {
        $user = User::factory()->create();
        $deviceName = substr(hash('sha256', 'Symfony'), 0, 32);
        $token = $user->createToken($deviceName)->plainTextToken;

        $this->apiPost('/auth/refresh', [], [
            'Authorization' => 'Bearer '.$token,
            'User-Agent' => 'DifferentBrowser',
        ])->assertStatus(403)
            ->assertJson([
                'success' => false,
                'message' => 'Invalid device',
            ]);
    }

    public function test_refresh_expired_session_deletes_token_and_returns_unauthorized(): void
    {
        $user = User::factory()->create();
        $deviceName = substr(hash('sha256', 'Symfony'), 0, 32);
        $createdToken = $user->createToken($deviceName);
        $createdToken->accessToken->forceFill([
            'last_used_at' => now()->subMinutes(config('sanctum.refresh_ttl', 30) + 1),
        ])->save();

        $this->apiPost('/auth/refresh', [], [
            'Authorization' => 'Bearer '.$createdToken->plainTextToken,
        ])->assertStatus(401)
            ->assertJson([
                'success' => false,
                'message' => 'Session expired, please login again',
            ]);

        $this->assertDatabaseMissing('personal_access_tokens', [
            'id' => $createdToken->accessToken->id,
        ]);
    }

    public function test_guest_cannot_refresh(): void
    {
        $this->apiPost('/auth/refresh')
            ->assertUnauthorized();
    }

    public function test_refresh_replaces_current_token_with_new_one(): void
    {
        $user = User::factory()->create();
        $deviceName = substr(hash('sha256', 'Symfony'), 0, 32);
        $createdToken = $user->createToken($deviceName);

        $this->apiPost('/auth/refresh', [], [
            'Authorization' => 'Bearer '.$createdToken->plainTextToken,
        ])->assertOk()
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'token',
                    'expires_in',
                    'user',
                ],
            ])
            ->assertJson([
                'data' => [
                    'expires_in' => config('sanctum.expiration', 60) * 60,
                ],
            ]);

        $this->assertDatabaseMissing('personal_access_tokens', [
            'id' => $createdToken->accessToken->id,
        ]);

        $this->assertDatabaseCount('personal_access_tokens', 1);
        $this->assertDatabaseHas('personal_access_tokens', [
            'tokenable_type' => User::class,
            'tokenable_id' => $user->id,
            'name' => $deviceName,
        ]);
    }

    public function test_refresh_cannot_be_called_with_invalid_token(): void
    {
        User::factory()->create();
        $user = User::factory()->create();
        $user->createToken('auth-token');

        $this->apiPost('/auth/refresh', [], [
            'Authorization' => 'Bearer invalid-token',
        ])->assertUnauthorized();

        $this->assertDatabaseCount('personal_access_tokens', 1);
    }
}
