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
        Schema::create('subscription', function (Blueprint $table) {
            $table->unsignedBigInteger('member_id');
            $table->unsignedBigInteger('owner_id');
            $table->boolean('is_verified')->default(false);
            $table->timestamps();
            $table->integer('amount')->default(0);
            $table->primary(['member_id', 'owner_id']);
            $table->char('payment_pin', 4)->nullable(); // 4 digit angka
            $table->timestamp('pin_generated_at')->nullable(); // waktu generate PIN

            $table->foreign('member_id')->references('id')->on('members')->onDelete('cascade');
            $table->foreign('owner_id')->references('id')->on('owners')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscription');
    }
};