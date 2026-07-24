<?php

namespace Tests\Feature\Web\Tag;

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

    public function test_guest_is_redirected_to_login_when_visiting_tags_create(): void
    {
        $response = $this->get(route('tags.create'));

        $response->assertRedirect(route('login'));
    }

    public function test_regular_user_can_visit_tags_create(): void
    {
        $response = $this->actingAs($this->regularUser())->get(route('tags.create'));

        $response->assertOk();
    }

    public function test_admin_can_visit_tags_create(): void
    {
        $response = $this->actingAs($this->admin())->get(route('tags.create'));

        $response->assertOk();
    }

    public function test_create_response_uses_tags_create_view(): void
    {
        $response = $this->actingAs($this->admin())->get(route('tags.create'));

        $response->assertViewIs('tags.create');
    }
}
