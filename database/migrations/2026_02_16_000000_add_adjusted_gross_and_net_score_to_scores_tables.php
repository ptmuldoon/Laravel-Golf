<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('scores', function (Blueprint $table) {
            $table->integer('adjusted_gross')->nullable()->after('strokes');
            $table->integer('net_score')->nullable()->after('adjusted_gross');
        });

        Schema::table('match_scores', function (Blueprint $table) {
            $table->integer('adjusted_gross')->nullable()->after('strokes');
        });
    }

    public function down(): void
    {
        Schema::table('scores', function (Blueprint $table) {
            $table->dropColumn(['adjusted_gross', 'net_score']);
        });

        Schema::table('match_scores', function (Blueprint $table) {
            $table->dropColumn('adjusted_gross');
        });
    }
};
