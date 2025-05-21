<?php

namespace Tests\Feature\Api;

use App\Models\Driver;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class DriverTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;
    protected User $regularUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->adminUser = User::factory()->create(['is_admin' => true]);
        $this->regularUser = User::factory()->create(['is_admin' => false]); 
    }

    /** @test */
    public function admin_can_list_drivers()
    {
        $team = Team::create([
            'id' => 1,
            'name' => 'Red Bull Racing',
            'principal' => 'Christian Horner',
            'base' => 'Milton Keynes, UK',
        ]);

        Driver::create([
            'id' => 1,
            'number' => 44,
            'name' => 'Lewis Hamilton',
            'team_id' => $team->id,
            'age' => 38,
            'country' => 'UK',
            'experience' => 'Veteran'
        ]);

        Driver::create([
            'id' => 2,
            'number' => 1,
            'name' => 'Max Verstappen',
            'team_id' => $team->id,
            'age' => 26,
            'country' => 'Netherlands',
            'experience' => 'Pro'
        ]);

        Sanctum::actingAs($this->adminUser, ['driver:view']);

        $response = $this->getJson('/api/drivers');

        $response->assertStatus(200)
            ->assertJsonStructure(['data', 'message', 'status']);

        $this->assertCount(2, $response->json('data'));
    }

    /** @test */
    public function regular_user_cannot_create_driver()
    {
        Sanctum::actingAs($this->regularUser);

        $payload = [
            'data' => [
                'attributes' => [
                    'number' => 5,
                    'name' => 'Sebastian Vettel',
                    'team_id' => 1,
                    'age' => 36,
                    'country' => 'Germany',
                    'experience' => 'Retired'
                ]
            ]
        ];

        $response = $this->postJson('/api/drivers', $payload);

        $response->assertStatus(403);
    }

    /** @test */
    public function admin_can_create_driver_with_valid_data()
    {
        $team = Team::create([
            'id' => 1,
            'name' => 'Red Bull Racing',
            'principal' => 'Christian Horner',
            'base' => 'Milton Keynes, UK',
        ]);

        Sanctum::actingAs($this->adminUser, ['driver:create']);

        $payload = [
            'data' => [
                'attributes' => [
                    'number' => 63,
                    'name' => 'George Russell',
                    'team' => $team->name,
                    'age' => 25,
                    'country' => 'UK',
                    'experience' => 'experienced'
                ]
            ]
        ];

        $response = $this->postJson('/api/drivers', $payload);

        $response->assertStatus(201)
            ->assertJsonFragment(['name' => 'George Russell']);

        $this->assertDatabaseHas('drivers', ['name' => 'George Russell']);
    }

    /** @test */
    public function admin_cannot_create_driver_with_invalid_data()
    {
        Sanctum::actingAs($this->adminUser, ['driver:create']);

        $payload = [
            'data' => [
                'attributes' => [
                    'age' => 22,
                    'team' => 1
                ]
            ]
        ];

        $response = $this->postJson('/api/drivers', $payload);

        $response->assertStatus(422)
            ->assertJsonFragment([
                'source' => 'data.attributes.number',
                'message' => 'The data.attributes.number field is required.'
            ]);
    }

    /** @test */
    public function admin_can_partially_update_driver()
    {
        $team = Team::create([
            'id' => 1,
            'name' => 'Red Bull Racing',
            'principal' => 'Christian Horner',
            'base' => 'Milton Keynes, UK',
        ]);

        $driver = Driver::create([
            'number' => 43,
            'name' => 'Franco Colapinto',
            'team_id' => $team->id,
            'age' => 21,
            'country' => 'Argentina',
            'experience' => 'rookie'
        ]);

        Sanctum::actingAs($this->adminUser, ['driver:update']);

        $payload = [
            'data' => [
                'attributes' => [
                    'age' => 35,
                ]
            ]
        ];

        $response = $this->patchJson("/api/drivers/{$driver->id}", $payload);

        $response->assertStatus(200)
            ->assertJsonPath('data.attributes.age', 35);

        $this->assertDatabaseHas('drivers', ['id' => $driver->id, 'age' => 35]);
    }

    /** @test */
    public function admin_can_replace_driver()
    {
        $team = Team::create([
            'id' => 1,
            'name' => 'Red Bull Racing',
            'principal' => 'Christian Horner',
            'base' => 'Milton Keynes, UK',
        ]);

        $driver = Driver::create([
            'number' => 10,
            'name' => 'Pierre Gasly',
            'team_id' => $team->id,
            'age' => 27,
            'country' => 'France',
            'experience' => 'Intermediate'
        ]);

        Sanctum::actingAs($this->adminUser, ['driver:replace']);

        $payload = [
            'data' => [
                'attributes' => [
                    'number' => 31,
                    'name' => 'Esteban Ocon',
                    'team' => $team->name,
                    'age' => 28,
                    'country' => 'France',
                    'experience' => 'experienced'
                ]
            ]
        ];

        $response = $this->putJson("/api/drivers/{$driver->id}", $payload);

        $response->assertStatus(200)
                ->assertJsonFragment(['name' => 'Esteban Ocon']);

        // Verifica que el driver fue actualizado correctamente sin cambiar su ID
        $this->assertDatabaseHas('drivers', [
            'id' => $driver->id,
            'number' => 31,
            'name' => 'Esteban Ocon',
            'team_id' => $team->id,
            'age' => 28,
            'country' => 'France',
            'experience' => 'experienced'
        ]);
    }
    /** @test */
    public function admin_can_delete_driver()
    {
        $team = Team::create([
            'id' => 1,
            'name' => 'Red Bull Racing',
            'principal' => 'Christian Horner',
            'base' => 'Milton Keynes, UK',
        ]);

        $driver = Driver::create([
            'number' => 55,
            'name' => 'Carlos Sainz',
            'team_id' => $team->id,
            'age' => 29,
            'country' => 'Spain',
            'experience' => 'Pro'
        ]);

        Sanctum::actingAs($this->adminUser, ['driver:delete']);

        $response = $this->deleteJson("/api/drivers/{$driver->id}");

        $response->assertStatus(204);

        $this->assertDatabaseMissing('drivers', ['id' => $driver->id]);
    }

    /** @test */
    public function unauthenticated_user_cannot_access_drivers()
    {
        $response = $this->getJson('/api/drivers');

        $response->assertStatus(401);
    }
}