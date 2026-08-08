<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('supplier_enquiry_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('supplier_enquiry_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            $table->string('description', 300);
            $table->unsignedInteger('quantity')->default(1);
            $table->string('status', 20)->default('pending')->index(); // pending|quoted|declined
            $table->decimal('offered_price', 12, 2)->nullable();
            $table->string('offered_currency', 10)->nullable();
            $table->unsignedInteger('lead_time_days')->nullable();
            $table->date('valid_until')->nullable();
            $table->string('supplier_notes', 300)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('supplier_enquiry_items');
    }
};