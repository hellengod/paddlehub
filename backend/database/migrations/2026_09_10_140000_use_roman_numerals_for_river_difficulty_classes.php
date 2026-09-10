<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * @var array<string, string>
     */
    private array $difficultyClasses = [
        'Classe 1' => 'Classe I',
        'Classe 2' => 'Classe II',
        'Classe 3' => 'Classe III',
        'Classe 4' => 'Classe IV',
        'Classe 5' => 'Classe V',
        'Classe 5+' => 'Classe V+',
    ];

    public function up(): void
    {
        foreach ($this->difficultyClasses as $numericValue => $romanValue) {
            DB::table('rivers')
                ->where('difficulty_class', $numericValue)
                ->update(['difficulty_class' => $romanValue]);
        }
    }

    public function down(): void
    {
        foreach ($this->difficultyClasses as $numericValue => $romanValue) {
            DB::table('rivers')
                ->where('difficulty_class', $romanValue)
                ->update(['difficulty_class' => $numericValue]);
        }
    }
};
