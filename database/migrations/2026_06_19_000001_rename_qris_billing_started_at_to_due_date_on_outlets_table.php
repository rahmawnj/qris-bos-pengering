<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('outlets', 'qris_billing_started_at')) {
            Schema::table('outlets', function (Blueprint $table) {
                $table->renameColumn('qris_billing_started_at', 'qris_billing_due_date');
            });

            return;
        }

        if (! Schema::hasColumn('outlets', 'qris_billing_due_date')) {
            Schema::table('outlets', function (Blueprint $table) {
                $table->date('qris_billing_due_date')->nullable()->after('qris_billing_enabled');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('outlets', 'qris_billing_due_date')) {
            Schema::table('outlets', function (Blueprint $table) {
                $table->renameColumn('qris_billing_due_date', 'qris_billing_started_at');
            });
        }
    }
};
