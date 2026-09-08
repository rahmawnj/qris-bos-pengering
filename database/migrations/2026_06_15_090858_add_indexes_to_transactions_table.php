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
        Schema::table('transactions', function (Blueprint $table) {
            $table->index('order_id');
            $table->index('status');
        });

        Schema::table('owners', function (Blueprint $table) {
            $table->index('brand_name');
        });

        Schema::table('outlets', function (Blueprint $table) {
            $table->index('outlet_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropIndex(['order_id']);
            $table->dropIndex(['status']);
        });

        Schema::table('owners', function (Blueprint $table) {
            $table->dropIndex(['brand_name']);
        });

        Schema::table('outlets', function (Blueprint $table) {
            $table->dropIndex(['outlet_name']);
        });
    }
};
