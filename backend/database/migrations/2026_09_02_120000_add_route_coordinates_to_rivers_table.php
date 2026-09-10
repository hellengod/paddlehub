<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rivers', function (Blueprint $table) {
            $table->json('route_coordinates')->nullable()->after('end_longitude');
        });
    }

    public function down(): void
    {
        Schema::table('rivers', function (Blueprint $table) {
            $table->dropColumn('route_coordinates');
        });
    }
};
