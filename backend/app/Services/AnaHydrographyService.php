<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class AnaHydrographyService
{
    private const QUERY_URL = 'https://portal1.snirh.gov.br/server/rest/services/dados_abertos/Hidrografia/MapServer/0/query';

    /**
     * @param array{west: float, south: float, east: float, north: float} $bounds
     * @return array<string, mixed>
     */
    public function fetch(array $bounds): array
    {
        return Cache::remember($this->cacheKey($bounds), now()->addHours(12), function () use ($bounds): array {
            $payload = Http::acceptJson()
                ->timeout(15)
                ->get(self::QUERY_URL, [
                    'where' => '1=1',
                    'geometry' => implode(',', $bounds),
                    'geometryType' => 'esriGeometryEnvelope',
                    'inSR' => 4326,
                    'spatialRel' => 'esriSpatialRelIntersects',
                    'outFields' => '*',
                    'returnGeometry' => 'true',
                    'outSR' => 4326,
                    'f' => 'json',
                ])
                ->throw()
                ->json();

            if (isset($payload['error'])) {
                throw new RuntimeException($payload['error']['message'] ?? 'A ANA recusou a consulta de hidrografia.');
            }

            return [
                'type' => 'FeatureCollection',
                'features' => collect($payload['features'] ?? [])
                    ->flatMap(function (array $feature): array {
                        return collect($feature['geometry']['paths'] ?? [])
                            ->filter(fn (array $path): bool => count($path) >= 2)
                            ->map(fn (array $path): array => [
                                'type' => 'Feature',
                                'properties' => $feature['attributes'] ?? [],
                                'geometry' => [
                                    'type' => 'LineString',
                                    'coordinates' => $path,
                                ],
                            ])
                            ->all();
                    })
                    ->values()
                    ->all(),
            ];
        });
    }

    /**
     * @param array{west: float, south: float, east: float, north: float} $bounds
     */
    private function cacheKey(array $bounds): string
    {
        return 'ana-hydrography:'.implode(':', array_map(
            fn (float $coordinate): string => number_format($coordinate, 5, '.', ''),
            $bounds,
        ));
    }
}
