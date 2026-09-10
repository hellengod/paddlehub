<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rivers', function (Blueprint $table) {
            $table->decimal('extension_km', 8, 2)->nullable()->after('description');
        });
    }

    public function down(): void
    {
        Schema::table('rivers', function (Blueprint $table) {
            $table->dropColumn('extension_km');
        });
    }
};
