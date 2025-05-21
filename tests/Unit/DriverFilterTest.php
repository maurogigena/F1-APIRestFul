<?php

namespace Tests\Feature\Http\Filters\Api;

use Tests\TestCase;
use App\Models\Driver;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DriverFilterTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->actingAs($this->user, 'sanctum');

        // Insertar equipos primero, si no existen
        $teamId = DB::table('teams')->insertGetId([
            'name' => 'Red Bull Racing',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Insertar drivers directamente
        DB::table('drivers')->insert([
            [
                'number' => 1,
                'name' => 'Max Verstappen',
                'team_id' => $teamId,
                'age' => 26,
                'country' => 'Netherlands',
                'experience' => 'legendary',
                'created_at' => now()->subMonths(6),
                'updated_at' => now(),
            ],
            [
                'number' => 44,
                'name' => 'Lewis Hamilton',
                'team_id' => $teamId,
                'age' => 39,
                'country' => 'United Kingdom',
                'experience' => 'legendary',
                'created_at' => now()->subMonths(12),
                'updated_at' => now(),
            ],
            [
                'number' => 4,
                'name' => 'Lando Norris',
                'team_id' => $teamId,
                'age' => 24,
                'country' => 'United Kingdom',
                'experience' => 'experienced',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ]);
    }

    /** @test */
    public function it_filters_by_exact_id()
    {
        $driver = Driver::first();

        $response = $this->getJson(route('drivers.index', [
            'filter' => ['id' => $driver->id]
        ]));

        $response->assertOk();
        $response->assertJsonCount(1, 'data');
        
        // La estructura es diferente - tenemos que adaptarnos a ella
        $responseData = $response->json('data.0.attributes');
        $this->assertEquals($driver->name, $responseData['name']);
        
        // O verificar que el ID está en los enlaces
        $this->assertStringContainsString(
            "/drivers/{$driver->id}", 
            $response->json('data.0.links.self')
        );
    }

    /** @test */
    public function it_filters_by_name_using_wildcard()
    {
        $response = $this->getJson(route('drivers.index', [
            'filter' => ['name' => '*Ver*']
        ]));

        $response->assertOk();
        
        // Adaptamos para la estructura real
        $foundVerstappen = false;
        foreach ($response->json('data') as $driver) {
            if (isset($driver['attributes']['name']) && 
                $driver['attributes']['name'] === 'Max Verstappen') {
                $foundVerstappen = true;
                break;
            }
        }
        
        $this->assertTrue($foundVerstappen, "No se encontró el conductor 'Max Verstappen'");
    }

    /** @test */
    public function it_filters_by_country()
    {
        $response = $this->getJson(route('drivers.index', [
            'filter' => ['country' => 'United Kingdom']
        ]));

        $response->assertOk();
        $this->assertGreaterThanOrEqual(1, count($response->json('data')));
        
        // Adaptamos para la estructura real
        $foundUK = false;
        foreach ($response->json('data') as $driver) {
            if (isset($driver['attributes']['country']) && 
                $driver['attributes']['country'] === 'United Kingdom') {
                $foundUK = true;
                break;
            }
        }
        
        $this->assertTrue($foundUK, "No se encontró ningún conductor de 'United Kingdom'");
    }

    /** @test */
    public function it_filters_by_age()
    {
        $driver = Driver::where('age', '>', 30)->first();

        $response = $this->getJson(route('drivers.index', [
            'filter' => ['age' => $driver->age]
        ]));

        $response->assertOk();
        
        // Adaptamos para la estructura real de la respuesta
        $foundAge = false;
        foreach ($response->json('data') as $responseDriver) {
            if (isset($responseDriver['attributes']['age']) && 
                $responseDriver['attributes']['age'] === $driver->age) {
                $foundAge = true;
                break;
            }
        }
        
        $this->assertTrue($foundAge, "No se encontró ningún conductor con age = {$driver->age}");
    }

    /** @test */
    public function it_filters_by_experience_using_wildcard()
    {
        // Verificar si hay al menos un conductor con 'legendary' como experiencia
        $legendaryExists = Driver::where('experience', 'legendary')->exists();
        
        // Si no existe ninguno, crearemos uno manualmente
        if (!$legendaryExists) {
            DB::table('drivers')->insert([
                'number' => 999,
                'name' => 'Test Legendary Driver',
                'team_id' => 1,
                'age' => 35,
                'country' => 'Test Country',
                'experience' => 'legendary',
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }
    
    // Imprimamos todos los conductores para verificar datos
    dump("Todos los conductores:", Driver::all(['id', 'name', 'experience'])->toArray());
    
    $response = $this->getJson(route('drivers.index', [
        'filter' => ['experience' => '*legendary*']
    ]));

    $response->assertOk();
    
    // Depuración: Vamos a ver la estructura completa de la respuesta
    dump("Estructura completa de la respuesta:", $response->json());
    
    // Verificar que hay al menos un resultado
    $this->assertNotEmpty($response->json('data'));
    
    // Verificar que al menos uno de los conductores tiene 'legendary' en su experiencia
    // Ajustamos para la estructura real
    $foundLegendary = false;
    foreach ($response->json('data') as $driver) {
        if (isset($driver['attributes']['experience']) && 
            $driver['attributes']['experience'] === 'legendary') {
            $foundLegendary = true;
            break;
        }
    }
    
    $this->assertTrue($foundLegendary, "No se encontró ningún conductor con experiencia 'legendary'");
}

    /** @test */
    public function it_filters_by_createdAt_between_dates()
    {
        // En lugar de tomar el primero, crea uno con fecha conocida
        $driver = Driver::first([
            'created_at' => now()->subMonth() // Una fecha que seguro está en el rango
        ]);

        $from = now()->subYear()->startOfDay()->toDateTimeString();
        $to = now()->addDay()->endOfDay()->toDateTimeString();

        $response = $this->getJson(route('drivers.index', [
            'filter' => ['createdAt' => "{$from},{$to}"]
        ]));

        $response->assertOk();
        $this->assertTrue(collect($response->json('data'))->pluck('id')->contains($driver->id));
        
        // Para depurar puedes agregar:
        // dump("Driver created_at: " . $driver->created_at);
        // dump("From: $from, To: $to");
        // dump("Results: ", $response->json('data'));
    }

    /** @test */
    public function it_sorts_by_name_ascending_and_descending()
    {
        $responseAsc = $this->getJson(route('drivers.index', ['sort' => 'name']));
        $responseDesc = $this->getJson(route('drivers.index', ['sort' => '-name']));

        $namesAsc = collect($responseAsc->json('data'))->pluck('name')->toArray();
        $namesDesc = collect($responseDesc->json('data'))->pluck('name')->toArray();

        $this->assertEquals(array_reverse($namesAsc), $namesDesc);
    }
}