<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // credit_balance was already added directly in create_customers_table (#9).
        // Guard prevents a duplicate-column crash on fresh migrate:fresh --seed runs.
        if (Schema::hasColumn('customers', 'credit_balance')) {
            return;
        }

        Schema::table('customers', function (Blueprint $table) {
            $table->decimal('credit_balance', 12, 2)->default(0)->after('outstanding_balance');
        });
    }

    public function down(): void
    {
        // Do not drop — the column is owned by the create_customers_table migration.
        // Dropping here would cause create_customers_table's down() to fail on rollback.
    }
};