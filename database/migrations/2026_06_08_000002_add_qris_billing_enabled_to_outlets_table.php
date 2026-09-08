<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('outlets', function (Blueprint $table) {
            $table->boolean('qris_billing_enabled')
                ->default(false)
                ->after('status')
                ->comment('Menentukan apakah outlet memakai sistem perpanjangan QRIS.');
        });
    }

    public function down(): void
    {
        Schema::table('outlets', function (Blueprint $table) {
            $table->dropColumn('qris_billing_enabled');
        });
    }
};
