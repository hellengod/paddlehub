<?php

namespace Tests\Feature\River;

use App\Models\River;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RiverTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_list_rivers(): void
    {
        $this->authenticateUser();
        $river = River::factory()->create([
            'name' => 'Rio do Peixe',
            'city' => 'Socorro',
            'state' => 'SP',
            'extension_km' => 7.0,
            'start_latitude' => -22.5904,
            'start_longitude' => -46.5251,
            'end_latitude' => -22.6412,
            'end_longitude' => -46.6124,
            'route_coordinates' => [
                [-46.5251, -22.5904],
                [-46.56, -22.62],
                [-46.6124, -22.6412],
            ],
        ]);
        $response = $this
            ->withHeader('Origin', config('app.url'))
            ->getJson('/api/rivers');

        $response
            ->assertOk()
            ->assertJson([
                'message' => 'Rios recuperados com sucesso',
            ])
            ->assertJsonPath('data.rivers.0.name', 'Rio do Peixe')
            ->assertJsonPath('data.rivers.0.city', 'Socorro')
            ->assertJsonPath('data.rivers.0.state', 'SP')
            ->assertJsonPath('data.rivers.0.extensionKm', 7)
            ->assertJsonPath('data.rivers.0.createdBy.id', $river->created_by)
            ->assertJsonPath('data.rivers.0.createdBy.name', $river->creator->name)
            ->assertJsonPath('data.rivers.0.canManage', false);
    }

    public function test_authenticated_user_can_create_river(): void
    {
        $user = $this->authenticateUser();

        $response = $this
            ->withHeader('Origin', config('app.url'))
            ->postJson('/api/rivers', [
                'name' => 'Rio Paraibuna',
                'city' => 'Juiz de Fora',
                'state' => 'mg',
                'difficulty_class' => 'Classe III',
                'description' => 'Trecho inicial mapeado para a primeira versao do cadastro.',
                'extension_km' => 12.7,
                'start_latitude' => -21.7642,
                'start_longitude' => -43.3496,
                'end_latitude' => -21.8012,
                'end_longitude' => -43.4123,
                'route_coordinates' => [
                    [-43.3496, -21.7642],
                    [-43.37, -21.79],
                    [-43.4123, -21.8012],
                ],
            ]);
        $response
            ->assertCreated()
            ->assertJson([
                'message' => 'Rio cadastrado com sucesso',
            ])
            ->assertJsonPath('data.river.name', 'Rio Paraibuna')
            ->assertJsonPath('data.river.state', 'MG')
            ->assertJsonPath('data.river.difficultyClass', 'Classe III')
            ->assertJsonPath('data.river.extensionKm', 12.7)
            ->assertJsonCount(3, 'data.river.routeCoordinates')
            ->assertJsonPath('data.river.createdBy.id', $user->id)
            ->assertJsonPath('data.river.createdBy.name', $user->name)
            ->assertJsonPath('data.river.canManage', true);

        $this->assertDatabaseHas('rivers', [
            'name' => 'Rio Paraibuna',
            'city' => 'Juiz de Fora',
            'state' => 'MG',
            'difficulty_class' => 'Classe III',
            'extension_km' => 12.7,
            'end_latitude' => -21.8012,
            'end_longitude' => -43.4123,
            'created_by' => $user->id,
        ]);
    }

    public function test_creator_can_update_own_river(): void
    {
        $user = $this->authenticateUser();
        $river = River::factory()->create(['created_by' => $user->id]);

        $response = $this
            ->withHeader('Origin', config('app.url'))
            ->putJson("/api/rivers/{$river->id}", $this->validRiverPayload([
                'name' => 'Rio atualizado',
            ]));

        $response
            ->assertOk()
            ->assertJsonPath('data.river.name', 'Rio atualizado')
            ->assertJsonPath('data.river.createdBy.id', $user->id)
            ->assertJsonPath('data.river.canManage', true);

        $this->assertDatabaseHas('rivers', [
            'id' => $river->id,
            'name' => 'Rio atualizado',
            'created_by' => $user->id,
        ]);
    }

    public function test_user_cannot_update_another_users_river(): void
    {
        $this->authenticateUser();
        $river = River::factory()->create();

        $this
            ->withHeader('Origin', config('app.url'))
            ->putJson("/api/rivers/{$river->id}", $this->validRiverPayload())
            ->assertForbidden();
    }

    public function test_creator_can_delete_own_river(): void
    {
        $user = $this->authenticateUser();
        $river = River::factory()->create(['created_by' => $user->id]);

        $this
            ->withHeader('Origin', config('app.url'))
            ->deleteJson("/api/rivers/{$river->id}")
            ->assertNoContent();

        $this->assertDatabaseMissing('rivers', ['id' => $river->id]);
    }

    public function test_user_cannot_delete_another_users_river(): void
    {
        $this->authenticateUser();
        $river = River::factory()->create();

        $this
            ->withHeader('Origin', config('app.url'))
            ->deleteJson("/api/rivers/{$river->id}")
            ->assertForbidden();

        $this->assertDatabaseHas('rivers', ['id' => $river->id]);
    }

    public function test_guest_cannot_list_rivers(): void
    {
        $response = $this
            ->withHeader('Origin', config('app.url'))
            ->getJson('/api/rivers');

        $response->assertUnauthorized();
    }

    public function test_detailed_route_retains_all_bends_and_distance_after_saving(): void
    {
        $this->authenticateUser();

        // A synthetic 7 km winding path whose endpoints are only 4.3 km apart.
        $kmPerDegree = 111.19492664455873;
        $segments = 600;
        $dx = 4.3 / $segments;
        $dy = sqrt((7 / $segments) ** 2 - $dx ** 2);
        $coordinates = array_map(fn (int $index): array => [
            $index * $dx / $kmPerDegree,
            ($index % 2) * $dy / $kmPerDegree,
        ], range(0, $segments));

        $response = $this->withHeader('Origin', config('app.url'))
            ->postJson('/api/rivers', [
                'name' => 'Percurso com curvas',
                'city' => 'Socorro',
                'state' => 'SP',
                'extension_km' => 7,
                'start_longitude' => $coordinates[0][0],
                'start_latitude' => $coordinates[0][1],
                'end_longitude' => $coordinates[$segments][0],
                'end_latitude' => $coordinates[$segments][1],
                'route_coordinates' => $coordinates,
            ]);

        $response->assertCreated()->assertJsonCount(601, 'data.river.routeCoordinates');
        $this->assertEquals(7.0, $response->json('data.river.extensionKm'));

        $river = River::findOrFail($response->json('data.river.id'));
        $this->assertCount(601, $river->route_coordinates);
        $this->assertEquals(7.0, $river->extensionKm());
    }

    public function test_guest_cannot_create_river(): void
    {
        $response = $this
            ->withHeader('Origin', config('app.url'))
            ->postJson('/api/rivers', [
                'name' => 'Rio Teste',
                'city' => 'Socorro',
                'state' => 'SP',
                'start_latitude' => -22.591,
                'start_longitude' => -46.523,
                'end_latitude' => -22.603,
                'end_longitude' => -46.541,
            ]);

        $response->assertUnauthorized();
    }

    public function test_store_validates_required_fields(): void
    {
        $this->authenticateUser();

        $response = $this
            ->withHeader('Origin', config('app.url'))
            ->postJson('/api/rivers', [
                'name' => '',
                'city' => '',
                'state' => 'Sao Paulo',
                'start_latitude' => 130,
                'start_longitude' => -300,
                'end_latitude' => 95,
                'end_longitude' => -250,
            ]);

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors([
                'name',
                'city',
                'state',
                'extension_km',
                'start_latitude',
                'start_longitude',
                'end_latitude',
                'end_longitude',
                'route_coordinates',
            ]);
    }

    public function test_store_validates_distinct_start_and_end_points(): void
    {
        $this->authenticateUser();

        $response = $this
            ->withHeader('Origin', config('app.url'))
            ->postJson('/api/rivers', [
                'name' => 'Rio Duplicado',
                'city' => 'Socorro',
                'state' => 'SP',
                'extension_km' => 3.5,
                'start_latitude' => -22.591,
                'start_longitude' => -46.523,
                'end_latitude' => -22.591,
                'end_longitude' => -46.523,
                'route_coordinates' => [
                    [-46.523, -22.591],
                    [-46.523, -22.591],
                ],
            ]);

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors([
                'end_latitude',
            ]);
    }

    public function test_store_validates_route_endpoints(): void
    {
        $this->authenticateUser();

        $response = $this
            ->withHeader('Origin', config('app.url'))
            ->postJson('/api/rivers', [
                'name' => 'Rio com percurso invalido',
                'city' => 'Juquitiba',
                'state' => 'SP',
                'extension_km' => 4.8,
                'start_latitude' => -23.93,
                'start_longitude' => -47.01,
                'end_latitude' => -23.95,
                'end_longitude' => -47.03,
                'route_coordinates' => [
                    [-47.02, -23.94],
                    [-47.03, -23.95],
                ],
            ]);

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['route_coordinates']);
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function validRiverPayload(array $overrides = []): array
    {
        return array_replace([
            'name' => 'Rio do Peixe',
            'city' => 'Socorro',
            'state' => 'SP',
            'difficulty_class' => 'Classe III',
            'description' => 'Trecho cadastrado pelo autor.',
            'extension_km' => 7,
            'start_latitude' => -22.614025,
            'start_longitude' => -46.458126,
            'end_latitude' => -22.616321,
            'end_longitude' => -46.489542,
            'route_coordinates' => [
                [-46.458126, -22.614025],
                [-46.47, -22.615],
                [-46.489542, -22.616321],
            ],
        ], $overrides);
    }
}
