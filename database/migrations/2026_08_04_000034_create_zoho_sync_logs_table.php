<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('zoho_sync_logs', function (Blueprint $table) {
            $table->id();
            $table->morphs('syncable');
            $table->string('entity_type', 40)->index();
            $table->string('operation', 20)->default('create');
            $table->string('status', 20)->default('pending')->index(); // pending|success|failed
            $table->string('zoho_id', 60)->nullable();
            $table->json('payload')->nullable();
            $table->string('error', 500)->nullable();
            $table->unsignedInteger('attempts')->default(0);
            $table->timestamp('last_attempted_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('zoho_sync_logs');
    }
};