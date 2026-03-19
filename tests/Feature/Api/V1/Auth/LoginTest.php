<?php

namespace Tests\Feature\Api\V1\Auth;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\With;
use Tests\Feature\Api\V1\ApiTestCase;


class LoginTest extends ApiTestCase
{
    public function test_user_can_login_with_valid_data (): void
    {
        $user = User::factory()->create([
            'email' => 'messi@gmail.com',
            'password' => 'Password123!',
        ]);

        $response = $this->apiPost('/auth/login', [
            'email' => 'messi@gmail.com',
            'password' => 'Password123!',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'token',
                    'user' => ['id', 'name', 'email', 'role'],
                ],
            ]);
    }
}
