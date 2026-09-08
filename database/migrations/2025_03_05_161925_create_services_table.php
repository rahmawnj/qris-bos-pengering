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
        Schema::create('services', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('outlet_id');
            $table->foreign('outlet_id')
                ->references('id')
                ->on('outlets')
                ->onDelete('cascade');
            $table->enum('unit', ['kg', 'pcs', 'liter', 'hour', 'unit'])->default('kg');

            $table->string('name');
            $table->decimal('price', 10, 2);

            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('services');
    }
};
