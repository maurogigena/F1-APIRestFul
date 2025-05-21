<?php

namespace Tests\Feature\Api;

use App\Models\Circuit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class CircuitTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;
    protected User $regularUser;

    protected function setUp(): void
    {
        parent::setUp();

        // Creamos un admin con todos los permisos
        $this->adminUser = User::factory()->create([
            'is_admin' => true,
        ]);
        // Creamos un usuario normal sin permisos admin
        $this->regularUser = User::factory()->create([
            'is_admin' => false,
        ]);
    }

    /** @test */
    public function admin_can_list_circuits()
    {
        // Creamos circuitos hardcodeados
        Circuit::create(['name' => 'Circuit 1', 'country' => 'Argentina', 'city' => 'Buenos Aires', 'record_driver_id' => 'Driver 1']);
        Circuit::create(['name' => 'Circuit 2', 'country' => 'Brasil', 'city' => 'Sao Paulo', 'record_driver_id' => 'Driver 2']);
        Circuit::create(['name' => 'Circuit 3', 'country' => 'Italia', 'city' => 'Monza', 'record_driver_id' => 'Driver 3']);

        Sanctum::actingAs($this->adminUser, ['circuit:view']);

        $response = $this->getJson('/api/circuits');

        $response->assertStatus(200)
            ->assertJsonStructure(['data', 'message', 'status']);
        $this->assertCount(3, $response->json('data'));
    }

    /** @test */
    public function regular_user_cannot_create_circuit()
    {
        Sanctum::actingAs($this->regularUser);

        $payload = [
            'data' => [
                'attributes' => [
                    'name' => 'Circuit Test',
                    'country' => 'Testland',
                    'city' => 'Testville',
                    'record_driver' => 'Test Driver',
                ]
            ]
        ];

        $response = $this->postJson('/api/circuits', $payload);

        $response->assertStatus(403); // Forbidden because no admin
    }

    /** @test */
    public function admin_can_create_circuit_with_valid_data()
    {
        Sanctum::actingAs($this->adminUser, ['circuit:create']);

        $payload = [
            'data' => [
                'attributes' => [
                    'name' => 'Circuit Test',
                    'country' => 'Testland',
                    'city' => 'Testville',
                    'recordDriver' => 'Test Driver',
                ]
            ]
        ];

        $response = $this->postJson('/api/circuits', $payload);

        $response->assertStatus(201)
            ->assertJsonFragment(['name' => 'Circuit Test']);
        $this->assertDatabaseHas('circuits', ['name' => 'Circuit Test']);
    }

    /** @test */
    public function admin_cannot_create_circuit_with_invalid_data()
    {
        Sanctum::actingAs($this->adminUser, ['circuit:create']);

        $payload = [
            'data' => [
                'attributes' => [
                    'country' => 'Testland',
                    'city' => 'Testville',
                    'recordDriver' => 'Test Driver',
                ]
            ]
        ];

        $response = $this->postJson('/api/circuits', $payload);

        $response->assertStatus(422)
            ->assertJsonFragment([
                'source' => 'data.attributes.name',
                'message' => 'The data.attributes.name field is required.'
            ]);
    }

    /** @test */
    public function admin_can_update_circuit_partially()
    {
        Sanctum::actingAs($this->adminUser, ['circuit:update']);

        $circuit = Circuit::create([
            'name' => 'Old Circuit',
            'country' => 'Oldland',
            'city' => 'Old City',
            'record_driver_id' => 'Old Driver',
        ]);

        $payload = [
            'data' => [
                'attributes' => [
                    'city' => 'New City',
                ]
            ]
        ];

        $response = $this->patchJson("/api/circuits/{$circuit->id}", $payload);

        $response->assertStatus(200)
            ->assertJsonPath('data.attributes.city', 'New City');
        $this->assertDatabaseHas('circuits', ['id' => $circuit->id, 'city' => 'New City']);
    }

    /** @test */
    public function admin_can_replace_circuit()
    {
        Sanctum::actingAs($this->adminUser, ['circuit:replace']);

        $circuit = Circuit::create([
            'name' => 'Old Circuit',
            'country' => 'Oldland',
            'city' => 'Old City',
            'record_driver_id' => 'Old Driver',
        ]);

        $payload = [
            'data' => [
                'attributes' => [
                    'name' => 'Replaced Circuit',
                    'country' => 'ReplaceLand',
                    'city' => 'Replace City',
                    'recordDriver' => 'New Driver',
                ]
            ]
        ];

        $response = $this->putJson("/api/circuits/{$circuit->id}", $payload);

        $response->assertStatus(200)
            ->assertJsonFragment(['name' => 'Replaced Circuit']);
        $this->assertDatabaseHas('circuits', ['id' => $circuit->id, 'name' => 'Replaced Circuit']);
    }

    /** @test */
    public function admin_can_delete_circuit()
    {
        Sanctum::actingAs($this->adminUser, ['circuit:delete']);

        $circuit = Circuit::create([
            'name' => 'Delete Me',
            'country' => 'Deleteland',
            'city' => 'Delete City',
            'record_driver_id' => 'Delete Driver',
        ]);

        $response = $this->deleteJson("/api/circuits/{$circuit->id}");

        $response->assertStatus(204);
        $this->assertDatabaseMissing('circuits', ['id' => $circuit->id]);
    }

    /** @test */
    public function unauthenticated_user_cannot_access_circuits()
    {
        $response = $this->getJson('/api/circuits');
        $response->assertStatus(401); // Unauthorized
    }
}