<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('match_players', function (Blueprint $table) {
            $table->dropUnique(['match_id', 'player_id']);
        });
    }

    public function down(): void
    {
        Schema::table('match_players', function (Blueprint $table) {
            $table->unique(['match_id', 'player_id']);
        });
    }
};
