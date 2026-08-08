<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->boolean('whatsapp_opted_out')->default(false)->after('whatsapp');
            $table->timestamp('whatsapp_last_messaged_at')->nullable()->after('whatsapp_opted_out');
        });
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn(['whatsapp_opted_out', 'whatsapp_last_messaged_at']);
        });
    }
};