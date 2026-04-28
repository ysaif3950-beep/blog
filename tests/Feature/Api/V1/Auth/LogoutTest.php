<?php

namespace Tests\Feature\Api\V1\Auth;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Tests\Feature\Api\V1\ApiTestCase;

class LogoutTest extends ApiTestCase
{
    public function test_user_can_logout(): void
    {
        $user = User::factory()->create();
        $createdToken = $user->createToken('auth-token');

        $response = $this->apiPost('/auth/logout', [], [
            'Authorization' => 'Bearer '.$createdToken->plainTextToken,
        ]);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Logged out successfully')
            ->assertJsonMissingPath('data');

        $this->assertDatabaseMissing('personal_access_tokens', [
            'id' => $createdToken->accessToken->id,
        ]);
    }

    public function test_logout_deletes_only_the_current_access_token(): void
    {
        $user = User::factory()->create();
        $currentToken = $user->createToken('current-device');
        $otherToken = $user->createToken('other-device');

        $response = $this->apiPost('/auth/logout', [], [
            'Authorization' => 'Bearer '.$currentToken->plainTextToken,
        ]);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Logged out successfully');

        $this->assertDatabaseMissing('personal_access_tokens', [
            'id' => $currentToken->accessToken->id,
        ]);

        $this->assertDatabaseHas('personal_access_tokens', [
            'id' => $otherToken->accessToken->id,
            'tokenable_type' => User::class,
            'tokenable_id' => $user->id,
            'name' => 'other-device',
        ]);
    }

    public function test_logged_out_token_cannot_be_used_again(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('auth-token')->plainTextToken;

        $this->apiPost('/auth/logout', [], [
            'Authorization' => 'Bearer '.$token,
        ])->assertOk();

        Auth::forgetGuards();

        $this->apiGet('/auth/user', [
            'Authorization' => 'Bearer '.$token,
        ])->assertUnauthorized();
    }

    public function test_user_cannot_logout_twice_with_the_same_token(): void
    {
        $user = User::factory()->create();
        $createdToken = $user->createToken('auth-token');

        $this->apiPost('/auth/logout', [], [
            'Authorization' => 'Bearer '.$createdToken->plainTextToken,
        ])->assertOk();

        Auth::forgetGuards();

        $this->apiPost('/auth/logout', [], [
            'Authorization' => 'Bearer '.$createdToken->plainTextToken,
        ])->assertUnauthorized();

        $this->assertDatabaseMissing('personal_access_tokens', [
            'id' => $createdToken->accessToken->id,
        ]);
    }

    public function test_logout_does_not_delete_other_users_tokens(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $UserToken = $user->createToken('auth-token');
        $otherUserToken = $otherUser->createToken('auth-token');

        $this->apiPost('/auth/logout', [], [
            'Authorization' => 'Bearer '.$UserToken->plainTextToken,
        ])->assertOk();

        $this->assertDatabaseMissing('personal_access_tokens', [
            'id' => $UserToken->accessToken->id,
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

    public function test_guest_cannot_logout(): void
    {
        $this->apiPost('/auth/logout')
            ->assertUnauthorized();

        $this->assertDatabaseCount('personal_access_tokens', 0);
    }
 public function test_user_cannot_logout_with_invalid_token(): void
{
    $user = User::factory()->create();
    $user->createToken('auth-token');

    $this->apiPost('/auth/logout', [], [
        'Authorization' => 'Bearer invalid-token',
    ])->assertUnauthorized();

    $this->assertDatabaseCount('personal_access_tokens', 1);
}
}
