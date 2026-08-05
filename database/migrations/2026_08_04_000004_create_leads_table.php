<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leads', function (Blueprint $table) {
            $table->id();
            $table->string('name', 120);
            $table->string('company_name', 160)->nullable();
            $table->string('email', 160)->nullable();
            $table->string('phone', 30)->nullable();
            $table->string('division', 30)->index();
            $table->string('source', 40)->index();
            $table->string('customer_type', 40)->nullable();
            $table->string('vehicle_brand_category', 30)->nullable()->index();
            $table->string('status', 30)->index()->default('new');
            $table->string('priority', 20)->default('medium');
            $table->string('subject', 200)->nullable();
            $table->text('requirements')->nullable();
            $table->decimal('estimated_value', 12, 2)->nullable()->default(null);
            $table->unsignedInteger('score')->nullable(); // AI Lead Scoring (AI Engine module)
            $table->boolean('needs_triage')->default(false)->index();
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->unsignedBigInteger('customer_id')->nullable(); // FK added by Customer module
            $table->timestamp('last_contacted_at')->nullable();
            $table->timestamp('next_follow_up_at')->nullable()->index();
            $table->timestamp('closed_at')->nullable();
            $table->string('lost_reason', 255)->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leads');
    }
};