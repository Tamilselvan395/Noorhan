<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lead_capture_events', function (Blueprint $table) {
            $table->id();
            $table->string('source', 40)->index();
            $table->json('payload');
            $table->string('status', 20)->default('received')->index(); // received|processed|failed
            $table->foreignId('lead_id')->nullable()->constrained()->nullOnDelete();
            $table->string('error', 500)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lead_capture_events');
    }
};