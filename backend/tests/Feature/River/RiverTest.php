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
            'start_latitude' => -22.5904,
            'start_longitude' => -46.5251,
            'end_latitude' => -22.6412,
            'end_longitude' => -46.6124,
        ]);
        $expectedExtensionKm = River::calculateDistanceKm(
            -22.5904,
            -46.5251,
            -22.6412,
            -46.6124,
        );

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
            ->assertJsonPath('data.rivers.0.extensionKm', $expectedExtensionKm)
            ->assertJsonPath('data.rivers.0.createdBy.id', $river->created_by)
            ->assertJsonPath('data.rivers.0.createdBy.name', $river->creator->name);
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
                'start_latitude' => -21.7642,
                'start_longitude' => -43.3496,
                'end_latitude' => -21.8012,
                'end_longitude' => -43.4123,
            ]);
        $expectedExtensionKm = River::calculateDistanceKm(
            -21.7642,
            -43.3496,
            -21.8012,
            -43.4123,
        );

        $response
            ->assertCreated()
            ->assertJson([
                'message' => 'Rio cadastrado com sucesso',
            ])
            ->assertJsonPath('data.river.name', 'Rio Paraibuna')
            ->assertJsonPath('data.river.state', 'MG')
            ->assertJsonPath('data.river.extensionKm', $expectedExtensionKm)
            ->assertJsonPath('data.river.createdBy.id', $user->id)
            ->assertJsonPath('data.river.createdBy.name', $user->name);

        $this->assertDatabaseHas('rivers', [
            'name' => 'Rio Paraibuna',
            'city' => 'Juiz de Fora',
            'state' => 'MG',
            'end_latitude' => -21.8012,
            'end_longitude' => -43.4123,
            'created_by' => $user->id,
        ]);
    }

    public function test_guest_cannot_list_rivers(): void
    {
        $response = $this
            ->withHeader('Origin', config('app.url'))
            ->getJson('/api/rivers');

        $response->assertUnauthorized();
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
                'start_latitude',
                'start_longitude',
                'end_latitude',
                'end_longitude',
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
                'start_latitude' => -22.591,
                'start_longitude' => -46.523,
                'end_latitude' => -22.591,
                'end_longitude' => -46.523,
            ]);

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors([
                'end_latitude',
            ]);
    }
}
