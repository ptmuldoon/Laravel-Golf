<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('league_segments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('league_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->integer('start_week');
            $table->integer('end_week');
            $table->integer('display_order')->default(0);
            $table->timestamps();

            $table->unique(['league_id', 'name']);
            $table->index(['league_id', 'display_order']);
        });

        Schema::table('teams', function (Blueprint $table) {
            $table->foreignId('league_segment_id')
                ->nullable()
                ->after('league_id')
                ->constrained('league_segments')
                ->onDelete('cascade');
        });

        // Update unique constraint to allow same team name in different segments
        // Add new index first (satisfies league_id FK), then drop old one
        Schema::table('teams', function (Blueprint $table) {
            $table->unique(['league_id', 'league_segment_id', 'name']);
        });
        Schema::table('teams', function (Blueprint $table) {
            $table->dropUnique(['league_id', 'name']);
        });
    }

    public function down(): void
    {
        Schema::table('teams', function (Blueprint $table) {
            $table->dropUnique(['league_id', 'league_segment_id', 'name']);
            $table->unique(['league_id', 'name']);
            $table->dropConstrainedForeignId('league_segment_id');
        });

        Schema::dropIfExists('league_segments');
    }
};
