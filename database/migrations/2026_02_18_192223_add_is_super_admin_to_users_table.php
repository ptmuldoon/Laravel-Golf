<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('is_super_admin')->default(false)->after('is_admin');
        });

        // Promote the first admin user to super admin
        $firstAdmin = DB::table('users')->where('is_admin', true)->orderBy('id')->first();
        if ($firstAdmin) {
            DB::table('users')->where('id', $firstAdmin->id)->update(['is_super_admin' => true]);
        }
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('is_super_admin');
        });
    }
};
