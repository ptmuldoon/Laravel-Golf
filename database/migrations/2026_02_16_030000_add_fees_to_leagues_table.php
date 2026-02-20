<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('leagues', function (Blueprint $table) {
            $table->decimal('fee_per_player', 8, 2)->nullable()->after('is_active');
            $table->decimal('par3_payout', 8, 2)->nullable()->after('fee_per_player');
            $table->decimal('payout_1st_pct', 5, 2)->default(50)->after('par3_payout');
            $table->decimal('payout_2nd_pct', 5, 2)->default(30)->after('payout_1st_pct');
            $table->decimal('payout_3rd_pct', 5, 2)->default(20)->after('payout_2nd_pct');
        });
    }

    public function down(): void
    {
        Schema::table('leagues', function (Blueprint $table) {
            $table->dropColumn(['fee_per_player', 'par3_payout', 'payout_1st_pct', 'payout_2nd_pct', 'payout_3rd_pct']);
        });
    }
};
