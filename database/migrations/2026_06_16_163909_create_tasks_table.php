<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->text('description');
            $table->string('status');
            $table->string('priority');
            $table->date('due_date');
            $table->timestamps();

            $table->index(['project_id', 'id']);
            $table->index(['project_id', 'status', 'id']);
            $table->index(['project_id', 'priority', 'id']);
            $table->index(['project_id', 'due_date', 'id']);
        });
    }

    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropForeign(['project_id']);
        });

        Schema::dropIfExists('tasks');
    }
};
