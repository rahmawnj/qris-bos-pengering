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
     Schema::create('manual_transaction_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('transaction_id');
            $table->foreign('transaction_id')
                ->references('id')->on('transactions')
                ->onDelete('cascade');
            $table->unsignedBigInteger('service_id')->nullable();
            $table->foreign('service_id')
                ->references('id')->on('services')
                ->onDelete('set null');
            $table->json('addons')->nullable();
            $table->integer('service_price');

            $table->float('quantity')->default(1);
            $table->string('unit')->nullable();

            $table->dateTime('estimated_completion_at')->nullable();
            $table->string('customer_name')->nullable();
            $table->string('customer_phone_number')->nullable();
            $table->string('progress')->default('received');
            $table->string('cashier_name')->nullable();
            $table->text('notes')->nullable();
            $table->enum('payment_method', ['cash', 'non_cash'])->default('cash');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('manual_transaction_details');
    }
};
