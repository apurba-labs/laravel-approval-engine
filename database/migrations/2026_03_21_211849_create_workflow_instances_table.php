<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('workflow_instances', function (Blueprint $table) {
            $table->id();
            $table->string('module'); // e.g., 'requisition', 'leave'
            $table->integer('current_stage_order'); // tracks which stage it is currently at
            $table->string('status')->default('pending'); // pending, approved, rejected, completed

            // The dynamic snapshot of the record data
            $table->json('payload')->nullable(); 

            // Audit timestamps for analytics
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();

            $table->timestamps();

            // Index for faster dashboard and analytics queries
            $table->index(['module', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('workflow_instances');
    }
};
