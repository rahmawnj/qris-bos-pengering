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
        Schema::create('rfid_transaction_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('transaction_id');
            // Mode transaksi: 'payment' untuk potong saldo, 'topup' untuk pengisian saldo
            $table->enum('transaction_mode', ['payment', 'topup']);
            $table->string('card_number')->nullable();
            $table->timestamps();

            $table->foreign('transaction_id')
                  ->references('id')->on('transactions')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rfid_transaction_details');
    }
};
