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
        Schema::table('qris_transaction_details', function (Blueprint $table) {
            $table->enum('payment_provider', ['xendit', 'midtrans'])
                ->default('xendit')
                ->after('transaction_id');
            $table->string('gateway_reference')->nullable()->after('payment_provider');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('qris_transaction_details', function (Blueprint $table) {
            $table->dropColumn(['payment_provider', 'gateway_reference']);
        });
    }
};
