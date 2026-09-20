<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class River extends Model
{
    /** @use HasFactory<\Database\Factories\RiverFactory> */
    use HasFactory;

    public const DIFFICULTY_CLASSES = [
        'Classe I',
        'Classe II',
        'Classe III',
        'Classe IV',
        'Classe V',
        'Classe V+',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'city',
        'state',
        'difficulty_class',
        'description',
        'extension_km',
        'start_latitude',
        'start_longitude',
        'end_latitude',
        'end_longitude',
        'route_coordinates',
        'created_by',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'extension_km' => 'float',
            'start_latitude' => 'float',
            'start_longitude' => 'float',
            'end_latitude' => 'float',
            'end_longitude' => 'float',
            'route_coordinates' => 'array',
        ];
    }

    public function extensionKm(): float
    {
        if ($this->extension_km !== null) {
            return (float) $this->extension_km;
        }

        if (is_array($this->route_coordinates) && count($this->route_coordinates) >= 2) {
            return self::calculatePolylineDistanceKm($this->route_coordinates);
        }

        if (
            $this->start_latitude === null
            || $this->start_longitude === null
            || $this->end_latitude === null
            || $this->end_longitude === null
        ) {
            return 0.0;
        }

        return self::calculateDistanceKm(
            $this->start_latitude,
            $this->start_longitude,
            $this->end_latitude,
            $this->end_longitude,
        );
    }

    public static function calculateDistanceKm(
        float $startLatitude,
        float $startLongitude,
        float $endLatitude,
        float $endLongitude,
    ): float {
        return round(self::distanceBetweenCoordinates(
            $startLatitude,
            $startLongitude,
            $endLatitude,
            $endLongitude,
        ), 1);
    }

    /**
     * @param  array<int, array{0: float|int, 1: float|int}>  $coordinates
     */
    public static function calculatePolylineDistanceKm(array $coordinates): float
    {
        $distance = 0.0;

        for ($index = 1; $index < count($coordinates); $index++) {
            [$startLongitude, $startLatitude] = $coordinates[$index - 1];
            [$endLongitude, $endLatitude] = $coordinates[$index];
            $distance += self::distanceBetweenCoordinates(
                (float) $startLatitude,
                (float) $startLongitude,
                (float) $endLatitude,
                (float) $endLongitude,
            );
        }

        return round($distance, 1);
    }

    private static function distanceBetweenCoordinates(
        float $startLatitude,
        float $startLongitude,
        float $endLatitude,
        float $endLongitude,
    ): float {
        $earthRadiusKm = 6371;
        $latitudeDelta = deg2rad($endLatitude - $startLatitude);
        $longitudeDelta = deg2rad($endLongitude - $startLongitude);
        $startLatitudeRadians = deg2rad($startLatitude);
        $endLatitudeRadians = deg2rad($endLatitude);

        $a = sin($latitudeDelta / 2) ** 2
            + cos($startLatitudeRadians) * cos($endLatitudeRadians) * sin($longitudeDelta / 2) ** 2;

        $normalizedA = min(1.0, max(0.0, $a));

        return 2 * $earthRadiusKm * asin(sqrt($normalizedA));
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function wishlistedByUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'river_wishlists')
            ->withTimestamps();
    }
}
