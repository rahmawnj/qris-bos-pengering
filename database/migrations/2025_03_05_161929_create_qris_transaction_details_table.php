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
        Schema::create('qris_transaction_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('transaction_id');
            $table->string('payment_url');
            $table->string('qr_code_image');
            $table->string('device_code')->nullable();
            $table->string('service_type')->nullable();
            $table->timestamp('bypass_activation')->nullable();
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
        Schema::dropIfExists('qris_transaction_details');
    }
};