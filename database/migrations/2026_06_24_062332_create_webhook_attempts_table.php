<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('webhook_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('webhook_id')->constrained('webhooks')->cascadeOnDelete();
            $table->string('status')->index();
            $table->integer('http_code')->nullable();
            $table->integer('response_time');
            $table->text('error')->nullable();
            $table->timestamp('occurred_at');
        });
    }

    public function down(): void
    {
        Schema::table('webhook_attempts', function (Blueprint $table) {
            $table->dropForeign(['webhook_id']);
        });

        Schema::dropIfExists('webhook_attempts');
    }
};
