<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('qris_billing_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('outlet_id')->constrained('outlets')->onDelete('cascade');
            $table->string('billing_period', 7);
            $table->unsignedInteger('amount')->default(0);
            $table->string('status')->default('paid');
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();

            $table->unique(['outlet_id', 'billing_period']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('qris_billing_payments');
    }
};
