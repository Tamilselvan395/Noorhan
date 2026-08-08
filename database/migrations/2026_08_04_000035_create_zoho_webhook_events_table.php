<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('zoho_webhook_events', function (Blueprint $table) {
            $table->id();
            $table->string('event', 60)->index();
            $table->json('payload');
            $table->string('status', 20)->default('received');
            $table->string('error', 500)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('zoho_webhook_events');
    }
};