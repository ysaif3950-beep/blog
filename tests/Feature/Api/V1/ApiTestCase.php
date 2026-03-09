<?php

namespace Tests\Feature\Api\V1;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
    protected function apiGet(string $uri, array $headers = [])
    {
        return $this->getJson($this->baseUrl . $uri, $headers);
    }

    protected function apiPost(string $uri, array $data = [])
    {
        return $this->postJson($this->baseUrl . $uri, $data);
    }

    protected function apiPut(string $uri, array $data = [])
    {
        return $this->putJson($this->baseUrl . $uri, $data);
    }

    protected function apiDelete(string $uri)
    {
        return $this->deleteJson($this->baseUrl . $uri);
    }
}
