<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
        'start_latitude',
        'start_longitude',
        'end_latitude',
        'end_longitude',
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
            'start_latitude' => 'float',
            'start_longitude' => 'float',
            'end_latitude' => 'float',
            'end_longitude' => 'float',
        ];
    }

    public function extensionKm(): float
    {
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
        $earthRadiusKm = 6371;
        $latitudeDelta = deg2rad($endLatitude - $startLatitude);
        $longitudeDelta = deg2rad($endLongitude - $startLongitude);
        $startLatitudeRadians = deg2rad($startLatitude);
        $endLatitudeRadians = deg2rad($endLatitude);

        $a = sin($latitudeDelta / 2) ** 2
            + cos($startLatitudeRadians) * cos($endLatitudeRadians) * sin($longitudeDelta / 2) ** 2;

        $normalizedA = min(1.0, max(0.0, $a));
        $distance = 2 * $earthRadiusKm * asin(sqrt($normalizedA));

        return round($distance, 1);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
