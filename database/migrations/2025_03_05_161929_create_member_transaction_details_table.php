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
        Schema::create('member_transaction_details', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('transaction_id');
            $table->unsignedBigInteger('member_id');
            $table->timestamps();

            $table->foreign('transaction_id')
                ->references('id')->on('transactions')
                ->onDelete('cascade');

            $table->foreign('member_id')
                ->references('id')->on('members')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('member_transaction_details');
    }
};