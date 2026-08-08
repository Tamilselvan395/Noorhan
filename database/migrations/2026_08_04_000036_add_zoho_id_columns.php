<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['customers', 'invoices', 'quotations', 'sales_orders', 'payments'] as $table) {
            Schema::table($table, function (Blueprint $table) {
                $table->string('zoho_id', 60)->nullable()->index();
            });
        }
    }

    public function down(): void
    {
        foreach (['customers', 'invoices', 'quotations', 'sales_orders', 'payments'] as $table) {
            Schema::table($table, function (Blueprint $table) {
                $table->dropColumn('zoho_id');
            });
        }
    }
};