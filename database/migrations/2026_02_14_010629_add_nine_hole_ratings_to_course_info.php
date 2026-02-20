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
        Schema::table('course_info', function (Blueprint $table) {
            $table->decimal('rating_9_front', 4, 1)->nullable()->after('rating');
            $table->decimal('rating_9_back', 4, 1)->nullable()->after('rating_9_front');
            $table->decimal('slope_9_front', 5, 1)->nullable()->after('slope');
            $table->decimal('slope_9_back', 5, 1)->nullable()->after('slope_9_front');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('course_info', function (Blueprint $table) {
            $table->dropColumn(['rating_9_front', 'rating_9_back', 'slope_9_front', 'slope_9_back']);
        });
    }
};
