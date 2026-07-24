<?php

namespace Tests\Feature\Web\Tag;

use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StoreTest extends TestCase
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

    public function test_guest_is_redirected_to_login_when_storing_tag(): void
    {
        $response = $this->post(route('tags.store'), [
            'name' => 'Test Tag',
        ]);

        $response->assertRedirect(route('login'));
    }

    public function test_regular_user_can_store_tag(): void
    {
        $user = $this->regularUser();

        $response = $this->actingAs($user)->post(route('tags.store'), [
            'name' => 'New Tag',
        ]);

        $response->assertRedirect(route('tags.index'));
        $response->assertSessionHas('success', 'Tag created successfully');
        $this->assertDatabaseHas('tags', [
            'name' => 'New Tag',
            'user_id' => $user->id,
        ]);
    }

    public function test_admin_can_store_tag(): void
    {
        $admin = $this->admin();

        $response = $this->actingAs($admin)->post(route('tags.store'), [
            'name' => 'Admin Tag',
        ]);

        $response->assertRedirect(route('tags.index'));
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('tags', [
            'name' => 'Admin Tag',
            'user_id' => $admin->id,
        ]);
    }

    public function test_store_tag_requires_name(): void
    {
        $response = $this->actingAs($this->regularUser())->post(route('tags.store'), []);

        $response->assertSessionHasErrors('name');
    }

    public function test_store_tag_name_must_be_at_least_3_characters(): void
    {
        $response = $this->actingAs($this->regularUser())->post(route('tags.store'), [
            'name' => 'ab',
        ]);

        $response->assertSessionHasErrors('name');
    }

    public function test_store_tag_name_must_be_unique(): void
    {
        $user = $this->regularUser();
        Tag::factory()->create(['name' => 'Existing Tag', 'user_id' => $user->id]);

        $response = $this->actingAs($user)->post(route('tags.store'), [
            'name' => 'Existing Tag',
        ]);

        $response->assertSessionHasErrors('name');
    }

    public function test_store_tag_creates_tag_for_authenticated_user(): void
    {
        $user = $this->regularUser();

        $this->actingAs($user)->post(route('tags.store'), [
            'name' => 'My Tag',
        ]);

        $this->assertDatabaseHas('tags', [
            'name' => 'My Tag',
            'user_id' => $user->id,
        ]);
    }
}
