<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('whatsapp_campaigns', function (Blueprint $table) {
            $table->id();
            $table->string('name', 160);
            $table->string('audience_type', 20)->default('all'); // all|division|type
            $table->string('audience_value', 40)->nullable();
            $table->string('message_type', 20)->default('text'); // text|template|media
            $table->string('template_name', 80)->nullable();
            $table->text('body')->nullable();
            $table->string('media_url', 500)->nullable();
            $table->string('media_kind', 20)->nullable(); // image|document
            $table->string('status', 20)->default('draft')->index();
            $table->timestamp('scheduled_at')->nullable();
            $table->unsignedInteger('sent_count')->default(0);
            $table->unsignedInteger('failed_count')->default(0);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('whatsapp_campaigns');
    }
};