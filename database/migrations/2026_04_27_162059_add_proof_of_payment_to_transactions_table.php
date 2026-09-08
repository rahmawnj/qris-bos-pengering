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
            $table->string('proof_of_payment')->nullable()->after('qr_code_image');
            $table->enum('bypass_status', ['inactive', 'active', 'activated'])->nullable()->after('proof_of_payment');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('qris_transaction_details', function (Blueprint $table) {
            $table->dropColumn('proof_of_payment');
            $table->dropColumn('bypass_status');
        });
    }
};
