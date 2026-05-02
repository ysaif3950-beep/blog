<?php

namespace Tests\Feature\Api\V1\Auth;

use App\Models\User;
use App\Models\Post;    
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use Tests\Feature\Api\V1\ApiTestCase;

class GetProfileTest extends ApiTestCase
{
    public function test_get_profile_user_success(): void
    {
        $user = User::factory()
            ->has(Post::factory()->count(3))
            ->create();

        $this->actingAsUser($user);

        $response = $this->apiGet('/auth/user');

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'data' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role,
                ],
            ])
            ->assertJsonCount(3, 'data.posts');

        $response->assertJsonStructure([
            'success',
            'data' => [
                'id',
                'name',
                'email',
                'role',
                'posts' => [
                    '*' => [
                        'id',
                        'title',
                        'excerpt',
                        'description',
                        'image',
                        'created_at',
                        'updated_at',
                    ],
                ],
            ],
        ]);

        $this->assertArrayNotHasKey('password', $response->json('data'));
        $this->assertArrayNotHasKey('remember_token', $response->json('data'));
    }

    public function test_get_profile_user_without_posts(): void
    {
        $user = User::factory()->create();

        $this->actingAsUser($user);

        $response = $this->apiGet('/auth/user');

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'data' => [
                    'id' => $user->id,
                    'posts' => [],
                ],
            ])
            ->assertJsonCount(0, 'data.posts');
    }

    public function test_get_profile_returns_latest_user_data(): void
    {
        $user = User::factory()->create(['name' => 'Old Name']);

        $this->actingAsUser($user);
        $user->update(['name' => 'New Name']);

        $response = $this->apiGet('/auth/user');

        $response->assertOk()
            ->assertJsonPath('data.name', 'New Name');
    }

    public function test_get_profile_user_with_admin_role(): void
    {
        $user = User::factory()->create(['role' => 'admin']);

        $this->actingAsUser($user);

        $response = $this->apiGet('/auth/user');

        $response->assertOk()
            ->assertJsonPath('data.role', 'admin');
    }

    public function test_get_profile_without_authentication(): void
    {
        $response = $this->apiGet('/auth/user');

        $response->assertUnauthorized();
    }

    public function test_get_profile_with_invalid_token_returns_unauthorized(): void
    {
        $response = $this->apiGet('/auth/user', [
            'Authorization' => 'Bearer invalid-token',
        ]);

        $response->assertUnauthorized();
    }
}
