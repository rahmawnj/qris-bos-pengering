<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('owners', function (Blueprint $table) {
            $afterColumn = Schema::hasColumn('owners', 'qris_account_type') ? 'qris_account_type' : 'balance';

            if (! Schema::hasColumn('owners', 'midtrans_partner_environment')) {
                $table->string('midtrans_partner_environment', 20)->default('sandbox')->after($afterColumn);
            }

            if (! Schema::hasColumn('owners', 'midtrans_partner_id')) {
                $table->string('midtrans_partner_id')->nullable()->after('midtrans_partner_environment');
            }

            if (! Schema::hasColumn('owners', 'midtrans_partner_server_key')) {
                $table->text('midtrans_partner_server_key')->nullable()->after('midtrans_partner_id');
            }
        });

        Schema::table('outlets', function (Blueprint $table) {
            if (! Schema::hasColumn('outlets', 'midtrans_partner_merchant_id')) {
                $table->string('midtrans_partner_merchant_id')->nullable()->after('phone_number');
            }
        });
    }

    public function down(): void
    {
        Schema::table('outlets', function (Blueprint $table) {
            $table->dropColumn('midtrans_partner_merchant_id');
        });

        Schema::table('owners', function (Blueprint $table) {
            $table->dropColumn([
                'midtrans_partner_environment',
                'midtrans_partner_id',
                'midtrans_partner_server_key',
            ]);
        });
    }
};
