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
        Schema::create('topup_histories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('member_id'); // foreign key ke member
            $table->unsignedBigInteger('outlet_id')->nullable(); // outlet terkait, opsional
            $table->unsignedBigInteger('owner_id'); // foreign key ke owner
            $table->integer('amount'); // jumlah topup
            $table->timestamp('time')->useCurrent(); // waktu topup
            $table->string('timezone'); // misalnya: 'wib', 'wita', 'wit'
            $table->string('cashier_name')->nullable(); // nama kasir, opsional
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('member_id')->references('id')->on('members')->onDelete('cascade');
            $table->foreign('outlet_id')->references('id')->on('outlets')->onDelete('set null');
            $table->foreign('owner_id')->references('id')->on('owners')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('topup_histories');
    }
};
