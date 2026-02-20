<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('scoring_settings', function (Blueprint $table) {
            $table->id();
            $table->string('scoring_type');
            $table->string('outcome');
            $table->decimal('points', 5, 2)->default(0);
            $table->string('description')->nullable();
            $table->timestamps();

            $table->unique(['scoring_type', 'outcome']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('scoring_settings');
    }
};
