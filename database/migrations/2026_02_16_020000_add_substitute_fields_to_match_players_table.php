<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('match_players', function (Blueprint $table) {
            $table->foreignId('substitute_player_id')->nullable()->after('player_id')
                  ->constrained('players')->nullOnDelete();
            $table->string('substitute_name')->nullable()->after('substitute_player_id');
        });
    }

    public function down(): void
    {
        Schema::table('match_players', function (Blueprint $table) {
            $table->dropForeign(['substitute_player_id']);
            $table->dropColumn(['substitute_player_id', 'substitute_name']);
        });
    }
};
