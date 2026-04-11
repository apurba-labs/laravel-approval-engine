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
        Schema::create('workflow_notifications', function (Blueprint $table) {
            $table->id();

            // Core
            $table->string('module');
            $table->string('role')->nullable(); // legacy fallback only

            // Recipient (initial resolved target)
            $table->nullableMorphs('recipient');

            // Source of truth
            $table->foreignId('workflow_instance_id')
                ->constrained('workflow_instances')
                ->cascadeOnDelete();

            // Batch (group delivery)
            $table->foreignId('batch_id')
                ->nullable()
                ->constrained('workflow_batches')
                ->nullOnDelete();

            // -------------------------------
            // 🧠 Stage Snapshot
            // -------------------------------
            $table->foreignId('stage_id')
                ->nullable()
                ->constrained('workflow_stages')
                ->nullOnDelete();

            $table->integer('stage_order')->nullable();

            // -------------------------------
            // 🧠 Assignment Snapshot (CRITICAL)
            // -------------------------------
            $table->string('assign_type')->nullable(); // role, permission, user
            $table->string('assign_value', 255)->nullable();

            // Deterministic batching / auth
            $table->string('recipient_signature', 255)->nullable();

            // Final resolved entity (who actually got it)
            $table->nullableMorphs(
                'resolved_recipient',
                'idx_wf_notif_resolved_recipient'
            );

            // -------------------------------
            // 📬 Delivery State
            // -------------------------------
            $table->boolean('is_sent')->default(false);
            $table->timestamp('sent_at')->nullable();

            $table->string('status')
                ->default('pending')
                ->comment('pending, sent, failed, permanent_failed, escalated');

            $table->text('error')->nullable();

            // -------------------------------
            // 🔁 Retry System
            // -------------------------------
            $table->integer('retry_count')->default(0);
            $table->integer('max_retries')->default(3);
            $table->timestamp('next_retry_at')->nullable();

            // -------------------------------
            // 🚨 Escalation System
            // -------------------------------
            $table->timestamp('escalate_at')->nullable();

            $table->string('escalate_assign_type')->nullable();
            $table->string('escalate_assign_value', 255)->nullable();

            $table->timestamp('escalated_at')->nullable();

            $table->timestamps();

            // -------------------------------
            // Indexes
            // -------------------------------

            // Batch lookup
            $table->index(
                ['module', 'recipient_signature', 'is_sent'],
                'idx_notifications_batch_lookup'
            );

            // Retry queue
            $table->index(
                ['status', 'next_retry_at'],
                'idx_notifications_retry_queue'
            );

            // Escalation queue
            $table->index(
                ['status', 'escalate_at'],
                'idx_notifications_escalation_queue'
            );

            // Stage tracking
            $table->index(['stage_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('workflow_notifications');
    }
};
