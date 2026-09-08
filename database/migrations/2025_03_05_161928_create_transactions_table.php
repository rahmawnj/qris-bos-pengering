<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('owner_id');
            $table->unsignedBigInteger('outlet_id')->nullable();

            $table->string('order_id')->nullable();
            $table->integer('amount');
            $table->enum('timezone', ['wib', 'wita', 'wit']);
            $table->date('date');
            $table->time('time');
            $table->string('device_code')->nullable();

            $table->enum('type', ['manual', 'qris', 'member']);
            $table->string('status');
            $table->timestamps();

            $table->foreign('owner_id')
                ->references('id')->on('owners')
                ->onDelete('cascade');
            $table->foreign('outlet_id')
                ->references('id')->on('outlets')
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
