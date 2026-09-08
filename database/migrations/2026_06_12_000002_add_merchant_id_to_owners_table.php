<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('owners', function (Blueprint $table) {
            if (! Schema::hasColumn('owners', 'payment_account_type')) {
                $table->string('payment_account_type', 20)->default('general')->after('qris_account_type');
            }

            if (! Schema::hasColumn('owners', 'merchant_id')) {
                $table->string('merchant_id')->nullable()->after('payment_account_type');
            }
        });
    }

    public function down(): void
    {
        Schema::table('owners', function (Blueprint $table) {
            $table->dropColumn(['payment_account_type', 'merchant_id']);
        });
    }
};
