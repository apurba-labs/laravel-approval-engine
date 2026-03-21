<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('workflow_batches', function (Blueprint $table) {
            $table->id();
            $table->string('module');
            $table->integer('current_stage_order');
            $table->string('token')->unique();
            $table->json('payload')->nullable(); 
            $table->timestamp('window_start');
            $table->timestamp('window_end');
            $table->integer('item_count')->default(0);
            $table->string('status')->default('pending');
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->integer('reminder_count')->default(0);
            $table->timestamp('last_reminder_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('workflow_batches');
    }
};
