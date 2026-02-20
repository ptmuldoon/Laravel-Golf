<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('league_finances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('league_id')->constrained()->onDelete('cascade');
            $table->foreignId('player_id')->constrained()->onDelete('cascade');
            $table->enum('type', ['fee_paid', 'winnings', 'payout']);
            $table->decimal('amount', 8, 2);
            $table->date('date');
            $table->string('notes', 255)->nullable();
            $table->timestamps();

            $table->index(['league_id', 'player_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('league_finances');
    }
};
