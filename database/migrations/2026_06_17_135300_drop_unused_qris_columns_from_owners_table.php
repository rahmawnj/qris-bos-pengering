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
            $table->dropColumn([
                'qris_account_type',
                'midtrans_partner_environment',
                'midtrans_partner_id',
                'midtrans_partner_server_key'
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('owners', function (Blueprint $table) {
            $table->string('qris_account_type', 20)->default('general')->after('balance');
            $table->string('midtrans_partner_environment', 20)->default('sandbox')->after('qris_account_type');
            $table->string('midtrans_partner_id')->nullable()->after('midtrans_partner_environment');
            $table->text('midtrans_partner_server_key')->nullable()->after('midtrans_partner_id');
        });
    }
};
