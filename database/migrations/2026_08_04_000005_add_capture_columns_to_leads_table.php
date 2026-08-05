<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->string('utm_source', 100)->nullable()->after('lost_reason');
            $table->string('utm_medium', 100)->nullable()->after('utm_source');
            $table->string('utm_campaign', 160)->nullable()->after('utm_medium');
            $table->string('landing_url', 500)->nullable()->after('utm_campaign');
            $table->string('business_card_path', 500)->nullable()->after('landing_url');
        });
    }

    public function down(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->dropColumn(['utm_source', 'utm_medium', 'utm_campaign', 'landing_url', 'business_card_path']);
        });
    }
};