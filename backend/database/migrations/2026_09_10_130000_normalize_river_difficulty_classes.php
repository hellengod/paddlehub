<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * @var array<string, string>
     */
    private array $difficultyClasses = [
        'Classe I' => 'Classe 1',
        'Classe II' => 'Classe 2',
        'Classe III' => 'Classe 3',
        'Classe IV' => 'Classe 4',
        'Classe V' => 'Classe 5',
        'Classe V+' => 'Classe 5+',
    ];

    public function up(): void
    {
        foreach ($this->difficultyClasses as $oldValue => $newValue) {
            DB::table('rivers')
                ->where('difficulty_class', $oldValue)
                ->update(['difficulty_class' => $newValue]);
        }
    }

    public function down(): void
    {
        foreach ($this->difficultyClasses as $oldValue => $newValue) {
            DB::table('rivers')
                ->where('difficulty_class', $newValue)
                ->update(['difficulty_class' => $oldValue]);
        }
    }
};
