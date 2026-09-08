<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('qris_billing_payments', function (Blueprint $table) {
            // Tambah index biasa untuk outlet_id agar foreign key tidak error saat unique dropped
            $table->index('outlet_id');
            
            // Hapus index unik lama
            $table->dropUnique(['outlet_id', 'billing_period']);
            $table->dropColumn('billing_period');
            
            // Tambah kolom range tanggal
            $table->date('period_start')->after('outlet_id');
            $table->date('period_end')->after('period_start');
        });
    }

    public function down(): void
    {
        Schema::table('qris_billing_payments', function (Blueprint $table) {
            $table->dropColumn(['period_start', 'period_end']);
            $table->string('billing_period', 7)->after('outlet_id');
            $table->unique(['outlet_id', 'billing_period']);
            $table->dropIndex(['outlet_id']);
        });
    }
};
