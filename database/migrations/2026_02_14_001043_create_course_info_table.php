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
        Schema::create('course_info', function (Blueprint $table) {
            $table->id();
            $table->foreignId('golf_course_id')->constrained()->onDelete('cascade');
            $table->string('teebox');
            $table->decimal('slope', 5, 1);
            $table->decimal('rating', 4, 1);
            $table->integer('hole_number');
            $table->integer('par');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('course_info');
    }
};
