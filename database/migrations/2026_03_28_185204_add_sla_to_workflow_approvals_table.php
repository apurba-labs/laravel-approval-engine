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
        Schema::table('workflow_approvals', function (Blueprint $table) {
            $table->timestamp('assigned_at')->nullable()->after('status')
                  ->comment('When the approval was first assigned to the user');
            
            $table->timestamp('due_at')->nullable()->after('assigned_at')
                  ->comment('The deadline for this approval stage');
            
            $table->timestamp('completed_at')->nullable()->after('due_at')
                  ->comment('When the user actually took action (Approved/Rejected)');

            // Index for finding overdue approvals quickly
            $table->index(['status', 'due_at'], 'idx_approvals_sla_check');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('workflow_approvals', function (Blueprint $table) {
            $table->dropIndex('idx_approvals_sla_check');
            $table->dropColumn(['assigned_at', 'due_at', 'completed_at']);
        });
    }
};
