<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('qris_billing_payments', function (Blueprint $table) {
            if (!Schema::hasColumn('qris_billing_payments', 'proof_of_payment')) {
                $table->string('proof_of_payment')->nullable()->after('paid_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('qris_billing_payments', function (Blueprint $table) {
            $table->dropColumn('proof_of_payment');
        });
    }
};
