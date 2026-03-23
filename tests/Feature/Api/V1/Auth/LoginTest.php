<?php

namespace Tests\Feature\Api\V1\Auth;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Tests\Feature\Api\V1\ApiTestCase;

class LoginTest extends ApiTestCase
{
    public function test_user_can_login_with_valid_data(): void
    {
        $email = fake()->safeEmail();

        $user = User::factory()->create([
            'email' => $email,
            'password' => Hash::make('Password123!'),
        ]);

        $response = $this->apiPost('/auth/login', [
            'email' => $email,
            'password' => 'Password123!',
        ]);

        $token = $response->json('data.token');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'token',
                    'user' => ['id', 'name', 'email', 'role'],
                ],
            ])
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.user.id', $user->id)
            ->assertJsonPath('data.user.email', $email);

        $this->assertIsString($token);
        $this->assertNotSame('', $token);

        $this->assertDatabaseCount('personal_access_tokens', 1);
        $this->assertDatabaseHas('personal_access_tokens', [
            'tokenable_type' => User::class,
            'tokenable_id' => $user->id,
            'name' => 'auth-token',
        ]);
    }

    public function test_login_token_can_be_used_to_access_authenticated_routes(): void
    {
        $user = User::factory()->create([
            'email' => 'messi@gmail.com',
            'password' => Hash::make('Password123!'),
        ]);

        $loginResponse = $this->apiPost('/auth/login', [
            'email' => 'messi@gmail.com',
            'password' => 'Password123!',
        ]);

        $token = $loginResponse->json('data.token');

        $response = $this->apiGet('/auth/user', [
            'Authorization' => 'Bearer '.$token,
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.id', $user->id)
            ->assertJsonPath('data.email', $user->email);
    }

    public function test_login_does_not_expose_sensitive_fields_in_user_payload(): void
    {
        User::factory()->create([
            'email' => 'messi@gmail.com',
            'password' => Hash::make('Password123!'),
        ]);

        $response = $this->apiPost('/auth/login', [
            'email' => 'messi@gmail.com',
            'password' => 'Password123!',
        ]);

        $response->assertStatus(200)
            ->assertJsonMissingPath('data.user.password')
            ->assertJsonMissingPath('data.user.remember_token')
            ->assertJsonMissingPath('data.user.posts');
    }

    public function test_login_creates_a_new_token_for_each_successful_request(): void
    {
        $user = User::factory()->create([
            'email' => 'messi@gmail.com',
            'password' => Hash::make('Password123!'),
        ]);

        $firstResponse = $this->apiPost('/auth/login', [
            'email' => 'messi@gmail.com',
            'password' => 'Password123!',
        ]);

        $secondResponse = $this->apiPost('/auth/login', [
            'email' => 'messi@gmail.com',
            'password' => 'Password123!',
        ]);

        $this->assertNotSame(
            $firstResponse->json('data.token'),
            $secondResponse->json('data.token')
        );

        $this->assertDatabaseCount('personal_access_tokens', 2);
        $this->assertSame(2, $user->fresh()->tokens()->count());
    }

    public function test_login_accepts_uppercase_email_input(): void
    {
        $user = User::factory()->create([
            'email' => 'messi@gmail.com',
            'password' => Hash::make('Password123!'),
        ]);

        $response = $this->apiPost('/auth/login', [
            'email' => 'MESSI@GMAIL.COM',
            'password' => 'Password123!',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.user.id', $user->id)
            ->assertJsonPath('data.user.email', 'messi@gmail.com');
    }

    public function test_login_accepts_email_with_surrounding_spaces(): void
    {
        $user = User::factory()->create([
            'email' => 'messi@gmail.com',
            'password' => Hash::make('Password123!'),
        ]);

        $response = $this->apiPost('/auth/login', [
            'email' => '  messi@gmail.com  ',
            'password' => 'Password123!',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.user.id', $user->id)
            ->assertJsonPath('data.user.email', 'messi@gmail.com');
    }

    public function test_login_fails_with_missing_email(): void
    {
        $this->apiPost('/auth/login', [
            'password' => 'Password123!',
        ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_login_fails_with_missing_password(): void
    {
        $this->apiPost('/auth/login', [
            'email' => fake()->safeEmail(),
        ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    }

    public function test_login_fails_with_missing_both_fields(): void
    {
        $this->apiPost('/auth/login', [])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['email', 'password']);
    }

    public function test_login_fails_with_null_email(): void
    {
        $this->apiPost('/auth/login', [
            'email' => null,
            'password' => 'Password123!',
        ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_login_fails_with_null_password(): void
    {
        $this->apiPost('/auth/login', [
            'email' => fake()->safeEmail(),
            'password' => null,
        ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    }

    public function test_login_fails_with_empty_email(): void
    {
        $this->apiPost('/auth/login', [
            'email' => '',
            'password' => 'Password123!',
        ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_login_fails_with_email_containing_only_spaces(): void
    {
        $this->apiPost('/auth/login', [
            'email' => '   ',
            'password' => 'Password123!',
        ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_login_fails_with_empty_password(): void
    {
        $this->apiPost('/auth/login', [
            'email' => fake()->safeEmail(),
            'password' => '',
        ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    }

    public function test_login_fails_with_email_as_array(): void
    {
        $this->apiPost('/auth/login', [
            'email' => ['messi@gmail.com'],
            'password' => 'Password123!',
        ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_login_fails_with_password_as_array(): void
    {
        $this->apiPost('/auth/login', [
            'email' => fake()->safeEmail(),
            'password' => ['Password123!'],
        ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    }

    public function test_login_fails_with_invalid_email_format(): void
    {
        $this->apiPost('/auth/login', [
            'email' => 'invalid-email-format',
            'password' => 'Password123!',
        ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_login_fails_with_wrong_password(): void
    {
        User::factory()->create([
            'email' => 'messi@gmail.com',
            'password' => Hash::make('Password123!'),
        ]);

        $this->apiPost('/auth/login', [
            'email' => 'messi@gmail.com',
            'password' => 'WrongPassword123!',
        ])
            ->assertStatus(401)
            ->assertJson([
                'success' => false,
                'message' => 'Invalid credentials',
            ]);

        $this->assertDatabaseCount('personal_access_tokens', 0);
    }

    public function test_login_fails_with_password_containing_only_spaces(): void
    {
        User::factory()->create([
            'email' => 'messi@gmail.com',
            'password' => Hash::make('Password123!'),
        ]);

        $this->apiPost('/auth/login', [
            'email' => 'messi@gmail.com',
            'password' => '   ',
        ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['password']);

        $this->assertDatabaseCount('personal_access_tokens', 0);
    }

    public function test_login_fails_with_nonexistent_email(): void
    {
        $this->apiPost('/auth/login', [
            'email' => 'nonexistent@gmail.com',
            'password' => 'Password123!',
        ])
            ->assertStatus(401)
            ->assertJson([
                'success' => false,
                'message' => 'Invalid credentials',
            ]);

        $this->assertDatabaseCount('personal_access_tokens', 0);
    }

    public function test_login_is_rate_limited_after_five_attempts_from_the_same_ip(): void
    {
        for ($attempt = 1; $attempt <= 5; $attempt++) {
            $this->withServerVariables([
                'REMOTE_ADDR' => '203.0.113.10',
            ])->postJson('/api/v1/auth/login', [
                'email' => 'nonexistent@gmail.com',
                'password' => 'Password123!',
            ])->assertStatus(401);
        }

        $this->withServerVariables([
            'REMOTE_ADDR' => '203.0.113.10',
        ])->postJson('/api/v1/auth/login', [
            'email' => 'nonexistent@gmail.com',
            'password' => 'Password123!',
        ])->assertStatus(429)
            ->assertJsonPath('message', 'Too Many Attempts.');
    }

    public function test_login_rate_limit_is_scoped_per_ip_address(): void
    {
        for ($attempt = 1; $attempt <= 5; $attempt++) {
            $this->withServerVariables([
                'REMOTE_ADDR' => '203.0.113.10',
            ])->postJson('/api/v1/auth/login', [
                'email' => 'nonexistent@gmail.com',
                'password' => 'Password123!',
            ]);
        }

        $this->withServerVariables([
            'REMOTE_ADDR' => '203.0.113.10',
        ])->postJson('/api/v1/auth/login', [
            'email' => 'nonexistent@gmail.com',
            'password' => 'Password123!',
        ])->assertStatus(429);

        $this->withServerVariables([
            'REMOTE_ADDR' => '203.0.113.11',
        ])->postJson('/api/v1/auth/login', [
            'email' => 'nonexistent@gmail.com',
            'password' => 'Password123!',
        ])->assertStatus(401);
    }
}
