<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Drop the old unique constraint
        Schema::table('scoring_settings', function (Blueprint $table) {
            $table->dropUnique(['scoring_type', 'outcome']);
        });

        // Add league_id column (nullable first for data migration)
        Schema::table('scoring_settings', function (Blueprint $table) {
            $table->foreignId('league_id')->nullable()->after('id')->constrained('leagues')->onDelete('cascade');
        });

        // Duplicate existing global settings for each league
        $leagues = DB::table('leagues')->pluck('id');
        $globalSettings = DB::table('scoring_settings')->whereNull('league_id')->get();

        foreach ($leagues as $leagueId) {
            foreach ($globalSettings as $setting) {
                DB::table('scoring_settings')->insert([
                    'league_id' => $leagueId,
                    'scoring_type' => $setting->scoring_type,
                    'outcome' => $setting->outcome,
                    'points' => $setting->points,
                    'description' => $setting->description,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        // Delete the old global (null league_id) rows
        DB::table('scoring_settings')->whereNull('league_id')->delete();

        // Make league_id non-nullable and add new unique constraint
        Schema::table('scoring_settings', function (Blueprint $table) {
            $table->foreignId('league_id')->nullable(false)->change();
            $table->unique(['league_id', 'scoring_type', 'outcome']);
        });
    }

    public function down(): void
    {
        Schema::table('scoring_settings', function (Blueprint $table) {
            $table->dropUnique(['league_id', 'scoring_type', 'outcome']);
            $table->dropForeign(['league_id']);
            $table->dropColumn('league_id');
            $table->unique(['scoring_type', 'outcome']);
        });
    }
};
