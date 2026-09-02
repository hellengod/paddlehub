<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('rivers', function (Blueprint $table) {
            $table->decimal('end_latitude', 10, 7)->nullable();
            $table->decimal('end_longitude', 10, 7)->nullable();
        });

        DB::table('rivers')->update([
            'end_latitude' => DB::raw('start_latitude'),
            'end_longitude' => DB::raw('start_longitude'),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rivers', function (Blueprint $table) {
            $table->dropColumn([
                'end_latitude',
                'end_longitude',
            ]);
        });
    }
};
