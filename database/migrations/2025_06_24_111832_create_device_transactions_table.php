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
        Schema::create('device_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transaction_id')->constrained('transactions')->onDelete('cascade');
            $table->string('device_code');
            $table->string('service_type')->nullable()->comment('Service type: washer atau dryer');
            $table->timestamp('activated_at')->nullable();
            $table->boolean('status')->default(true);
            $table->timestamp('bypass_activation')->nullable()->comment('Waktu perangkat diaktifkan secara bypass');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('device_transactions');
    }
};