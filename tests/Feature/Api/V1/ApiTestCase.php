<?php

namespace Tests\Feature\Api\V1;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

abstract class ApiTestCase extends TestCase
{
    use RefreshDatabase;

    protected string $baseUrl = '/api/v1';

    /** يرجع user مسجل دخول مع Sanctum token */
    protected function actingAsUser(?User $user = null): User
    {
        $user ??= User::factory()->create();
        $this->actingAs($user, 'sanctum');

        return $user;
    }

    /** Helper لإرسال JSON requests */
    protected function apiGet(string $uri, array $headers = []): TestResponse
    {
        return $this->getJson($this->baseUrl.$uri, $headers);
    }

    protected function apiPost(string $uri, array $data = [], array $headers = []): TestResponse
    {
        return $this->postJson($this->baseUrl.$uri, $data, $headers);
    }

    protected function apiPut(string $uri, array $data = [], array $headers = []): TestResponse
    {
        return $this->putJson($this->baseUrl.$uri, $data, $headers);
    }

    protected function apiDelete(string $uri, array $headers = []): TestResponse
    {
        return $this->deleteJson($this->baseUrl.$uri, [], $headers);
    }
}
