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
        Schema::create('handicap_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('player_id')->constrained()->onDelete('cascade');
            $table->date('calculation_date');
            $table->decimal('handicap_index', 4, 1);
            $table->integer('rounds_used');
            $table->text('score_differentials')->nullable();
            $table->timestamps();

            $table->index(['player_id', 'calculation_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('handicap_history');
    }
};
