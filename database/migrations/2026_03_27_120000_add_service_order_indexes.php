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
        Schema::table('transactions', function (Blueprint $table) {
            $table->index(['outlet_id', 'type', 'created_at'], 'transactions_outlet_type_created_at_idx');
            $table->index(['owner_id', 'type', 'created_at'], 'transactions_owner_type_created_at_idx');
        });

        Schema::table('manual_transaction_details', function (Blueprint $table) {
            $table->index(['transaction_id', 'progress'], 'manual_transaction_tx_progress_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropIndex('transactions_outlet_type_created_at_idx');
            $table->dropIndex('transactions_owner_type_created_at_idx');
        });

        Schema::table('manual_transaction_details', function (Blueprint $table) {
            $table->dropIndex('manual_transaction_tx_progress_idx');
        });
    }
};
