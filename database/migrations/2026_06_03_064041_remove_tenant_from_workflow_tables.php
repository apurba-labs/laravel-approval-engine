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
        $tables = [
            'workflow_instances',
            'workflow_approvals',
            'workflow_logs',
            'workflow_notifications',
            'approval_tokens',
            'workflow_batches',
            'workflow_modules',
            'workflow_rules',
            'workflow_settings',
            'workflow_stages',
        ];

        foreach ($tables as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                if (Schema::hasColumn($table->getTable(), 'tenant_id')) {
                    $table->dropColumn('tenant_id');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tables = [
            'workflow_instances',
            'workflow_approvals',
            'workflow_logs',
            'workflow_notifications',
            'approval_tokens',
            'workflow_batches',
            'workflow_modules',
            'workflow_rules',
            'workflow_settings',
            'workflow_stages',
        ];

        foreach ($tables as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->ulid('tenant_id')->nullable()->index();
            });
        }
    }
};
