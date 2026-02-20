<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('par3_winners', function (Blueprint $table) {
            $table->id();
            $table->foreignId('league_id')->constrained()->onDelete('cascade');
            $table->integer('week_number');
            $table->integer('hole_number');
            $table->foreignId('player_id')->nullable()->constrained()->onDelete('set null');
            $table->string('distance')->nullable();
            $table->timestamps();

            $table->unique(['league_id', 'week_number', 'hole_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('par3_winners');
    }
};
