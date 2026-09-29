<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::rename('river_wishlists', 'river_paddling_lists');
    }

    public function down(): void
    {
        Schema::rename('river_paddling_lists', 'river_wishlists');
    }
};
