<?php

namespace Tests\Feature\Web\User;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CreateTest extends TestCase
{
    use RefreshDatabase;

    protected function admin(): User
    {
        return User::factory()->create(['role' => 'admin']);
    }

    protected function regularUser(): User
    {
        return User::factory()->create(['role' => 'user']);
    }

    public function test_guest_is_redirected_to_login_when_visiting_users_create(): void
    {
        $response = $this->get(route('users.create'));

        $response->assertRedirect(route('login'));
    }

    public function test_regular_user_is_forbidden_from_visiting_users_create(): void
    {
        $response = $this->actingAs($this->regularUser())->get(route('users.create'));

        $response->assertForbidden();
    }

    public function test_admin_can_visit_users_create(): void
    {
        $response = $this->actingAs($this->admin())->get(route('users.create'));

        $response->assertOk();
    }

    public function test_create_response_uses_users_create_view(): void
    {
        $response = $this->actingAs($this->admin())->get(route('users.create'));

        $response->assertViewIs('users.create');
    }
}
