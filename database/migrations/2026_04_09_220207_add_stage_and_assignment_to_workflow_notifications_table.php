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
        Schema::table('workflow_notifications', function (Blueprint $table) {
             // Stage snapshot
            $table->unsignedBigInteger('stage_id')->nullable()->after('role');
            $table->integer('stage_order')->nullable()->after('stage_id');

            // Assignment snapshot
            $table->string('assign_type')->nullable()->after('stage_order');
            $table->text('assign_value')->nullable()->after('assign_type');

            // Deterministic batching / auth signature
            $table->text('recipient_signature')->nullable()->after('error');

            // Actual resolved entity used for notification/approval
            $table->nullableMorphs('resolved_recipient');

            // Indexes
            $table->index(['stage_id', 'status']);
            $table->index(
                ['module', 'recipient_signature', 'is_sent'],
                'idx_notifications_batch_lookup'
            );

            // FK
            $table->foreign('stage_id')
                ->references('id')
                ->on('workflow_stages')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('workflow_notifications', function (Blueprint $table) {
            $table->dropForeign(['stage_id']);

            $table->dropColumn([
                'stage_id',
                'stage_order',
                'assign_type',
                'assign_value',
                'recipient_signature',
                'resolved_recipient_id',
                'resolved_recipient_type',
            ]);
        });
    }
};
