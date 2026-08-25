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
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
