<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('zoho_connections', function (Blueprint $table) {
            $table->id();
            $table->string('organization_id', 60);
            $table->string('client_id', 191);
            $table->text('client_secret_cipher');
            $table->text('refresh_token_cipher');
            $table->text('access_token_cipher')->nullable();
            $table->timestamp('token_expires_at')->nullable();
            $table->json('settings')->nullable(); // per-entity sync toggles
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('zoho_connections');
    }
};