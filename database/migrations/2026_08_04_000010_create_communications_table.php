<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('communications', function (Blueprint $table) {
            $table->id();
            $table->morphs('communicable');
            $table->string('channel', 20)->index();   // email|phone|whatsapp|meeting|sms
            $table->string('direction', 10);          // inbound|outbound
            $table->string('subject', 200)->nullable();
            $table->text('body')->nullable();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamp('occurred_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('communications');
    }
};