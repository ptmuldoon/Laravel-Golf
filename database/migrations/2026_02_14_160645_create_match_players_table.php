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
        Schema::create('match_players', function (Blueprint $table) {
            $table->id();
            $table->foreignId('match_id')->constrained()->onDelete('cascade');
            $table->foreignId('team_id')->constrained()->onDelete('cascade');
            $table->foreignId('player_id')->constrained()->onDelete('cascade');
            $table->decimal('handicap_index', 4, 1);
            $table->decimal('course_handicap', 4, 1);
            $table->integer('position_in_pairing')->default(1);
            $table->timestamps();

            $table->index(['match_id', 'team_id']);
            $table->unique(['match_id', 'player_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('match_players');
    }
};
