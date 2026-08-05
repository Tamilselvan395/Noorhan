<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->string('name', 160);
            $table->string('trade_license_no', 60)->nullable();
            $table->string('tax_number', 60)->nullable();
            $table->string('type', 40)->index();                 // CustomerType enum
            $table->string('status', 20)->default('active')->index();
            $table->string('division', 30)->index()->default('automotive');
            $table->string('email', 160)->nullable();
            $table->string('phone', 30)->nullable();
            $table->string('website', 200)->nullable();
            $table->text('address')->nullable();
            $table->string('city', 80)->nullable();
            $table->string('country', 80)->nullable();
            $table->foreignId('owner_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamp('last_activity_at')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('companies');
    }
};