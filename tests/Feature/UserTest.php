<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed con admin y 19 usuarios normales
        $this->artisan('db:seed');
    }

    /** @test */
    public function test_admin_can_index_users()
    {
        $admin = User::where('is_admin', true)->first();

        Sanctum::actingAs($admin, ['*']);

        $response = $this->getJson('/api/users');

        $response->assertOk()->assertJsonCount(15, 'data');
    }

    /** @test */
    public function test_normal_user_cannot_index_users()
    {
        $user = User::where('is_admin', false)->first();

        Sanctum::actingAs($user, ['*']);

        $response = $this->getJson('/api/users');

        $response->assertForbidden();
    }

    /** @test */
    public function test_admin_can_show_any_user()
    {
        $admin = User::where('is_admin', true)->first();
        $target = User::where('is_admin', false)->first();

        Sanctum::actingAs($admin, ['*']);

        $response = $this->getJson("/api/users/{$target->id}");

        $response->assertOk()->assertJsonPath('data.id', $target->id);
    }

    /** @test */
    public function test_normal_user_can_show_own_data_only()
    {
        $user = User::where('is_admin', false)->first();

        Sanctum::actingAs($user, ['*']);

        $response = $this->getJson("/api/users/{$user->id}");
        $response->assertOk()->assertJsonPath('data.id', $user->id);

        // Prueba contra otro usuario
        $otherUser = User::where('id', '!=', $user->id)->where('is_admin', false)->first();
        $response = $this->getJson("/api/users/{$otherUser->id}");

        $response->assertForbidden();
    }

    /** @test */
    public function test_admin_can_create_user()
    {
        $admin = User::where('is_admin', true)->first();

        Sanctum::actingAs($admin, ['*']);

        $response = $this->postJson('/api/users', [
            'data' => [
                'type' => 'users',
                'attributes' => [
                    'name' => 'Nuevo User',
                    'email' => 'nuevo@example.com',
                    'password' => 'password',
                    'isAdmin' => false,
                ],
            ],
        ]);

        $response->assertCreated()->assertJsonPath('data.attributes.email', 'nuevo@example.com');
    }

    /** @test */
    public function test_normal_user_cannot_create_user()
    {
        $user = User::where('is_admin', false)->first();

        Sanctum::actingAs($user, ['*']);

        $response = $this->postJson('/api/users', [
            'name' => 'No permitido',
            'email' => 'nope@example.com',
            'password' => 'password',
        ]);

        $response->assertForbidden();
    }

    /** @test */
    public function test_admin_can_update_any_user()
    {
        $admin = User::where('is_admin', true)->first();
        $target = User::where('is_admin', false)->first();

        Sanctum::actingAs($admin, ['*']);

        $response = $this->patchJson("/api/users/{$target->id}", [
            'data' => [
                'type' => 'users',
                'attributes' => [
                    'name' => 'Nombre actualizado por admin',
                ],
            ],
        ]);

        $response->assertOk()->assertJsonPath('data.attributes.name', 'Nombre actualizado por admin');
    }

    /** @test */
    public function test_normal_user_can_update_self_only()
    {
        $user = User::where('is_admin', false)->first();
        Sanctum::actingAs($user, ['*']);

        $response = $this->patchJson("/api/users/{$user->id}", [
            'data' => [
                'type' => 'users',
                'attributes' => [
                    'name' => 'Nombre actualizado por user',
                ],
            ],
        ]);

        $response->assertOk()->assertJsonPath('data.attributes.name', 'Nombre actualizado por user');

        $otherUser = User::where('id', '!=', $user->id)->where('is_admin', false)->first();

        $response = $this->patchJson("/api/users/{$otherUser->id}", [
            'name' => 'No deberia poder',
        ]);

        $response->assertForbidden();
    }

    /** @test */
    public function test_admin_can_replace_any_user()
    {
        $admin = User::where('is_admin', true)->first();
        $target = User::where('is_admin', false)->first();

        Sanctum::actingAs($admin, ['*']);

        $response = $this->putJson("/api/users/{$target->id}", [
            'data' => [
                'type' => 'users',
                'attributes' => [
                    'name' => 'Reemplazado',
                    'email' => 'reemplazado@example.com',
                    'password' => 'newpassword',
                    'isAdmin' => false,
                ],
            ],
        ]);

        $response->assertOk()->assertJsonPath('data.attributes.email', 'reemplazado@example.com');
    }

    /** @test */
    public function test_normal_user_can_replace_self_only()
    {
        $user = User::where('is_admin', false)->first();
        Sanctum::actingAs($user, ['*']);

        $response = $this->putJson("/api/users/{$user->id}", [
            'data' => [
                'type' => 'users',
                'attributes' => [
                    'name' => 'Reemplazo propio',
                    'email' => 'selfreplace@example.com',
                    'password' => 'password',
                ],
            ],
        ]);

        $response->assertOk()->assertJsonPath('data.attributes.email', 'selfreplace@example.com');

        $otherUser = User::where('id', '!=', $user->id)->first();

        $response = $this->putJson("/api/users/{$otherUser->id}", [
            'name' => 'Hackeo',
            'email' => 'hack@example.com',
            'password' => 'hackpass',
        ]);

        $response->assertForbidden();
    }

    /** @test */
    public function test_admin_can_delete_any_user()
    {
        $admin = User::where('is_admin', true)->first();
        $target = User::factory()->create();

        Sanctum::actingAs($admin, ['*']);

        $response = $this->deleteJson("/api/users/{$target->id}");

        $response->assertNoContent();
        $this->assertDatabaseMissing('users', ['id' => $target->id]);
    }

    /** @test */
    public function test_normal_user_can_delete_self_only()
    {
        // Usuario normal
        $user = User::where('is_admin', false)->first();

        // Usuario se autentica
        Sanctum::actingAs($user, ['*']);

        // Puede eliminarse a sí mismo
        $response = $this->deleteJson("/api/users/{$user->id}");
        $response->assertNoContent();
        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }

    /** @test */
    public function test_normal_user_cannot_delete_other_user()
    {
        // Preparo dos usuarios normales
        $user       = User::where('is_admin', false)->first();
        $otherUser  = User::factory()->create(['is_admin' => false]);

        // Autentico como $user
        Sanctum::actingAs($user, ['*']);

        // Intento eliminar a $otherUser
        $response = $this->deleteJson("/api/users/{$otherUser->id}");

        // Debe devolver 403 Forbidden
        $response->assertForbidden();

        // Y el otro usuario sigue existiendo en la base
        $this->assertDatabaseHas('users', ['id' => $otherUser->id]);
    }

    /** @test */
    public function test_admin_cannot_create_user_with_invalid_data()
    {
        $admin = User::where('is_admin', true)->first();
        Sanctum::actingAs($admin, ['*']);

        $response = $this->postJson('/api/users/', [
            'data' => [
                'type' => 'users',
                'attributes' => [
                    'name' => '',
                    'email' => 'no-es-un-email',
                    'password' => '',
                ],
            ],
        ]);

        $response->assertStatus(422);

        $errors = collect($response->json('errors'));

        $this->assertTrue($errors->contains(fn ($e) => $e['source'] === 'data.attributes.name'));
        $this->assertTrue($errors->contains(fn ($e) => $e['source'] === 'data.attributes.email'));
        $this->assertTrue($errors->contains(fn ($e) => $e['source'] === 'data.attributes.password'));
    }

    /** @test */
    public function test_admin_gets_404_on_non_existing_user()
    {
        $admin = User::where('is_admin', true)->first();
        Sanctum::actingAs($admin, ['*']);

        $response = $this->getJson('/api/users/99999');
        $response->assertNotFound();
    }

    /** @test */
    public function test_guest_cannot_access_any_user_endpoint()
    {
        $response = $this->getJson('/api/users');
        $response->assertUnauthorized();

        $response = $this->postJson('/api/users', []);
        $response->assertUnauthorized();
    }
}