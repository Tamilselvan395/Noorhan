<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('name', 120);
            $table->string('company_name', 160)->nullable();
            $table->string('zoho_id')->nullable()->index();
            $table->string('email', 160)->nullable()->index();
            $table->string('phone', 30)->nullable();
            $table->string('whatsapp', 30)->nullable();
            $table->string('type', 40)->index();                 // CustomerType enum
            $table->string('status', 20)->default('active')->index();
            $table->text('address')->nullable();
            $table->string('city', 80)->nullable();
            $table->string('country', 80)->nullable();
            $table->string('division', 30)->index()->default('automotive');
            $table->string('vehicle_brand_category', 30)->nullable();
            $table->unsignedBigInteger('company_id')->nullable(); // FK added by Company Management module
            $table->foreignId('lead_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('owner_id')->nullable()->constrained('users')->nullOnDelete();
            $table->decimal('credit_limit', 12, 2)->nullable();   // used by Zoho/credit engine later
            $table->decimal('credit_balance', 12, 2)->default(0); // <-- Add this
            $table->decimal('outstanding_balance', 12, 2)->default(0);
            $table->text('notes')->nullable();
            $table->timestamp('last_activity_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};