<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('rivers', function (Blueprint $table) {
            $table->id();
            $table->string('name', 120);
            $table->string('city', 120);
            $table->string('state', 2);
            $table->string('difficulty_class', 24)->nullable();
            $table->text('description')->nullable();
            $table->decimal('start_latitude', 10, 7);
            $table->decimal('start_longitude', 10, 7);
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();

            $table->index(['state', 'city']);
            $table->index('difficulty_class');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rivers');
    }
};
