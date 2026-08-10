<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // zoho_id was already included in every CREATE migration for these tables.
        // Guard makes this migration a no-op on fresh installs.
        foreach (['customers', 'invoices', 'quotations', 'sales_orders', 'payments'] as $tbl) {
            if (Schema::hasColumn($tbl, 'zoho_id')) {
                continue;
            }

            Schema::table($tbl, function (Blueprint $table) {
                $table->string('zoho_id', 60)->nullable()->index();
            });
        }
    }

    public function down(): void
    {
        // Do not drop — zoho_id is owned by each table's own CREATE migration.
    }
};