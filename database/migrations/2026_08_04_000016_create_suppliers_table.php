<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('suppliers', function (Blueprint $table) {
            $table->id();
            $table->string('name', 160);
            $table->string('contact_person', 120)->nullable();
            $table->string('email', 160)->nullable();
            $table->string('phone', 30)->nullable();
            $table->string('whatsapp', 30)->nullable();
            $table->string('website', 200)->nullable();
            $table->string('country', 80)->nullable();
            $table->string('city', 80)->nullable();
            $table->text('address')->nullable();
            $table->string('division', 30)->index();
            $table->string('status', 20)->default('active')->index();
            $table->string('payment_terms', 120)->nullable();
            $table->string('currency', 10)->default('USD');
            $table->foreignId('owner_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('suppliers');
    }
};