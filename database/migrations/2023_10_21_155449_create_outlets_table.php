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
        Schema::create('outlets', function (Blueprint $table) {
            $table->id();
            // $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('owner_id')->nullable()->constrained('owners')->onDelete('cascade'); // Relasi owner ke outlet
            $table->string('outlet_name')->nullable();
            $table->string('code')->unique();
            $table->string('address')->nullable();
            $table->string('phone_number')->nullable();
            $table->boolean('status')->default(false);
            $table->enum('timezone', ['WIB', 'WITA', 'WIT'])->default('WIB')->comment('Indonesian Time Zones: WIB (Western Indonesian Time), WITA (Central Indonesian Time), WIT (Eastern Indonesian Time)');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('outlets');
    }
};