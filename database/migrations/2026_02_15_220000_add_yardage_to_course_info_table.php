<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('course_info', function (Blueprint $table) {
            $table->integer('yardage')->nullable()->after('par');
        });
    }

    public function down(): void
    {
        Schema::table('course_info', function (Blueprint $table) {
            $table->dropColumn('yardage');
        });
    }
};
