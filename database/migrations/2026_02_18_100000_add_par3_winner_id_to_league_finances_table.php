<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('league_finances', function (Blueprint $table) {
            $table->foreignId('par3_winner_id')->nullable()->after('notes')
                ->constrained('par3_winners')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('league_finances', function (Blueprint $table) {
            $table->dropForeign(['par3_winner_id']);
            $table->dropColumn('par3_winner_id');
        });
    }
};
