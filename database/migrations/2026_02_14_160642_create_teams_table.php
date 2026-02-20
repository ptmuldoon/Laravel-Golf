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
        Schema::create('teams', function (Blueprint $table) {
            $table->id();
            $table->foreignId('league_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->foreignId('captain_id')->nullable()->constrained('players')->onDelete('set null');
            $table->integer('wins')->default(0);
            $table->integer('losses')->default(0);
            $table->integer('ties')->default(0);
            $table->timestamps();

            $table->unique(['league_id', 'name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('teams');
    }
};
