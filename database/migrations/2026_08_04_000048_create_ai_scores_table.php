<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_scores', function (Blueprint $table) {
            $table->id();
            $table->morphs('scoreable');
            $table->string('score_type', 30)->index(); // lead_score|health_score|churn_risk
            $table->decimal('score', 8, 2);
            $table->json('metadata')->nullable();
            $table->timestamp('computed_at');
            $table->timestamps();
            $table->index(['score_type', 'score']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_scores');
    }
};