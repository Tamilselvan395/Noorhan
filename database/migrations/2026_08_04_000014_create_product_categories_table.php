<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name', 120);
            $table->string('division', 30)->index();
            $table->string('description', 500)->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort')->default(0);
            $table->timestamps();
            $table->unique(['name', 'division']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_categories');
    }
};