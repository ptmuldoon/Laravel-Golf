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
        Schema::create('match_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('match_id')->constrained()->onDelete('cascade');
            $table->foreignId('winning_team_id')->nullable()->constrained('teams')->onDelete('cascade');
            $table->integer('holes_won_home')->default(0);
            $table->integer('holes_won_away')->default(0);
            $table->integer('holes_tied')->default(0);
            $table->decimal('team_points_home', 3, 1)->default(0);
            $table->decimal('team_points_away', 3, 1)->default(0);
            $table->timestamps();

            $table->unique('match_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('match_results');
    }
};
