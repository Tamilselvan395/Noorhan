<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('supplier_price_lists', function (Blueprint $table) {
            $table->id();
            $table->foreignId('supplier_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->decimal('price', 12, 2);
            $table->string('currency', 10)->default('USD');
            $table->unsignedInteger('min_qty')->default(1);
            $table->unsignedInteger('lead_time_days')->nullable();
            $table->date('valid_until')->nullable();
            $table->string('notes', 255)->nullable();
            $table->timestamps();
            $table->unique(['supplier_id', 'product_id', 'currency']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('supplier_price_lists');
    }
};