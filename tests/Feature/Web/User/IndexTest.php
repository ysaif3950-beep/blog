<?php

namespace Tests\Feature\Web\User;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IndexTest extends TestCase
{
    use RefreshDatabase;

    protected function createAdmin(): User
    {
        return User::factory()->create(['role' => 'admin']);
    }

    protected function createUser(): User
    {
        return User::factory()->create(['role' => 'user']);
    }

    public function test_unauthenticated_user_is_redirected_to_login(): void
    {
        $response = $this->get(route('users.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_regular_user_receives_403(): void
    {
        $response = $this->actingAs($this->createUser())->get(route('users.index'));

        $response->assertForbidden();
    }

    public function test_admin_can_access_users_index(): void
    {
        $response = $this->actingAs($this->createAdmin())->get(route('users.index'));

        $response->assertOk();
    }

    public function test_users_index_uses_correct_view(): void
    {
        $response = $this->actingAs($this->createAdmin())->get(route('users.index'));

        $response->assertViewIs('users.index');
    }

    public function test_users_index_view_has_paginated_users(): void
    {
        User::factory()->count(20)->create();

        $response = $this->actingAs($this->createAdmin())->get(route('users.index'));

        $response->assertViewHas('users');
        $users = $response->viewData('users');
        $this->assertTrue($users->hasPages());
        $this->assertEquals(15, $users->perPage());
    }

    public function test_users_are_ordered_by_id_descending(): void
    {
        User::factory()->count(3)->create(['role' => 'user']);

        $response = $this->actingAs($this->createAdmin())->get(route('users.index'));

        $users = $response->viewData('users');
        $userIds = $users->pluck('id')->toArray();
        $this->assertEquals($userIds, collect($userIds)->sortDesc()->values()->toArray());
    }
}
