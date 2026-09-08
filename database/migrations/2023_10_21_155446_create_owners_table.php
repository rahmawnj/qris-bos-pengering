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
      Schema::create('owners', function (Blueprint $table) {
            $table->id();
            $table->string('brand_name');
            $table->string('brand_logo')->nullable();
            $table->string('brand_email')->unique();
            $table->string('brand_phone')->nullable();
            $table->string('address');
            $table->integer('balance');

            // Kolom Baru 1: Masa Aktif Akun (Timestamp Kapan Akun Berakhir)
            $table->timestamp('account_expires_at')->nullable();

            // Kolom Baru 2: Status Akun (Boolean, default 'true' atau aktif)
            // Gunakan 'default(true)' agar akun otomatis aktif saat dibuat.
            $table->boolean('status')->default(true);

            $table->unsignedBigInteger('user_id')->nullable();
            $table->foreign('user_id')
                ->references('id')->on('users')
                ->onDelete('cascade');

            $table->json('receipt_config')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('owners');
    }
};
