<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('sku', 60)->unique();
            $table->string('name', 160);
            $table->foreignId('category_id')->nullable()->constrained('product_categories')->nullOnDelete();
            $table->string('division', 30)->index();
            $table->string('brand', 80)->nullable();
            $table->text('description')->nullable();
            $table->string('unit', 20)->default('pcs');
            $table->decimal('cost_price', 12, 2)->default(0);
            $table->decimal('sale_price', 12, 2)->default(0);
            $table->decimal('tax_rate', 5, 2)->default(5.00); // UAE VAT default
            $table->json('attributes')->nullable();            // viscosity, size, fitment… extensible
            $table->integer('min_stock')->nullable();         // inventory module later
            $table->string('image_path', 500)->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};