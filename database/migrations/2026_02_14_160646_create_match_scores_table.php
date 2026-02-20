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
        Schema::create('match_scores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('match_player_id')->constrained()->onDelete('cascade');
            $table->integer('hole_number');
            $table->integer('strokes');
            $table->integer('net_score');
            $table->timestamps();

            $table->unique(['match_player_id', 'hole_number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('match_scores');
    }
};
