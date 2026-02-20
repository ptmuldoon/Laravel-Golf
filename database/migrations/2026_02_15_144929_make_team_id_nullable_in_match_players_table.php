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
        Schema::table('match_players', function (Blueprint $table) {
            // Drop the foreign key constraint first
            $table->dropForeign(['team_id']);

            // Make team_id nullable
            $table->foreignId('team_id')->nullable()->change();

            // Re-add the foreign key constraint
            $table->foreign('team_id')->references('id')->on('teams')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('match_players', function (Blueprint $table) {
            // Drop the foreign key constraint
            $table->dropForeign(['team_id']);

            // Make team_id not nullable again
            $table->foreignId('team_id')->nullable(false)->change();

            // Re-add the foreign key constraint
            $table->foreign('team_id')->references('id')->on('teams')->onDelete('cascade');
        });
    }
};
