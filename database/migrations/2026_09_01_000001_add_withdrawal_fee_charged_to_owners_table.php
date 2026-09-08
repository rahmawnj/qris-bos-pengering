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
        Schema::table('owners', function (Blueprint $table) {
            if (! Schema::hasColumn('owners', 'withdrawal_fee_charged')) {
                $table->boolean('withdrawal_fee_charged')->default(true)->after('merchant_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('owners', function (Blueprint $table) {
            if (Schema::hasColumn('owners', 'withdrawal_fee_charged')) {
                $table->dropColumn('withdrawal_fee_charged');
            }
        });
    }
};
