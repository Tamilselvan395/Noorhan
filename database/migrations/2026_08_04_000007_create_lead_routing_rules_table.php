<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lead_routing_rules', function (Blueprint $table) {
            $table->id();
            $table->string('division', 30)->index();
            $table->string('condition_type', 30);          // vehicle_brand | customer_type | default
            $table->string('condition_value', 40)->nullable();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedInteger('priority')->default(100);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lead_routing_rules');
    }
};