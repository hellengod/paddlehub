<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class LocationSearchService
{
    /**
     * @return array<int, array{id: int|string, label: string, latitude: float, longitude: float, bounds: array{west: float, south: float, east: float, north: float}|null}>
     */
    public function search(string $query): array
    {
        $normalizedQuery = trim(preg_replace('/\s+/', ' ', $query) ?? $query);
        $cacheKey = 'location-search:'.hash('sha256', mb_strtolower($normalizedQuery));

        $cachedPlaces = Cache::get($cacheKey);
        if (is_array($cachedPlaces)) {
            return $cachedPlaces;
        }

        return Cache::lock('nominatim-search-request', 15)->block(10, function () use ($cacheKey, $normalizedQuery): array {
            $cachedPlaces = Cache::get($cacheKey);
            if (is_array($cachedPlaces)) {
                return $cachedPlaces;
            }

            $lastRequestAt = (float) Cache::get('nominatim-search:last-request-at', 0);
            $remainingDelay = 1 - (microtime(true) - $lastRequestAt);
            if ($remainingDelay > 0) {
                usleep((int) ceil($remainingDelay * 1_000_000));
            }

            $response = Http::acceptJson()
                ->withHeaders([
                    'User-Agent' => config('services.nominatim.user_agent'),
                ])
                ->timeout(12)
                ->get(config('services.nominatim.url'), [
                    'q' => $normalizedQuery,
                    'format' => 'jsonv2',
                    'addressdetails' => 1,
                    'accept-language' => 'pt-BR,pt',
                    'limit' => 5,
                ]);

            Cache::put('nominatim-search:last-request-at', microtime(true), now()->addMinute());

            $places = collect($response->throw()->json())
                ->filter(fn (mixed $place): bool => is_array($place)
                    && isset($place['display_name'], $place['lat'], $place['lon']))
                ->map(function (array $place): array {
                    $bounds = $place['boundingbox'] ?? null;

                    return [
                        'id' => $place['place_id'] ?? $place['osm_id'] ?? $place['display_name'],
                        'label' => $place['display_name'],
                        'latitude' => (float) $place['lat'],
                        'longitude' => (float) $place['lon'],
                        'bounds' => is_array($bounds) && count($bounds) === 4 ? [
                            'west' => (float) $bounds[2],
                            'south' => (float) $bounds[0],
                            'east' => (float) $bounds[3],
                            'north' => (float) $bounds[1],
                        ] : null,
                    ];
                })
                ->values()
                ->all();

            Cache::put($cacheKey, $places, now()->addDay());

            return $places;
        });
    }
}
