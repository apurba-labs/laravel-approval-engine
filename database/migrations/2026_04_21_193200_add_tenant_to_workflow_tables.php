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
        Schema::table('workflow_instances', function (Blueprint $table) {
            $table->ulid('tenant_id')->index();
        });

        Schema::table('workflow_approvals', function (Blueprint $table) {
            $table->ulid('tenant_id')->index();
        });

        Schema::table('workflow_logs', function (Blueprint $table) {
            $table->ulid('tenant_id')->index();
        });

        Schema::table('workflow_notifications', function (Blueprint $table) {
            $table->ulid('tenant_id')->index();
        });

        Schema::table('approval_tokens', function (Blueprint $table) {
            $table->ulid('tenant_id')->index();
        });

        Schema::table('workflow_batches', function (Blueprint $table) {
            $table->ulid('tenant_id')->index();
        });

        Schema::table('workflow_modules', function (Blueprint $table) {
            $table->ulid('tenant_id')->index();
        });

        Schema::table('workflow_rules', function (Blueprint $table) {
            $table->ulid('tenant_id')->index();
        });

        Schema::table('workflow_settings', function (Blueprint $table) {
            $table->ulid('tenant_id')->index();
        });

        Schema::table('workflow_stages', function (Blueprint $table) {
            $table->ulid('tenant_id')->index();
        });
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
                $table->dropColumn('tenant_id');
            });
        }
    }
};
