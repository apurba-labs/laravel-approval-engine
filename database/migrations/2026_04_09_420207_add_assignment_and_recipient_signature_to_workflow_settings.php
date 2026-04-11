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
        Schema::table('workflow_settings', function (Blueprint $table) {
            // Assignment snapshot
            $table->string('assign_type')->nullable()->after('role')->comment('role, permission, user');
            $table->string('assign_value', 255)->nullable()->after('assign_type')->comment('Role slug, permission key, or user id');

            // Deterministic batching / auth signature
            $table->string('recipient_signature_pattern', 255)->nullable()->after('assign_value')->comment('Pattern for generating recipient signature');

            // Drop the unique index by its custom name
            $table->dropUnique('uidx_role_module');

            $table->index(['module', 'assign_type', 'assign_value'], 'workflow_settings_assignment_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('workflow_settings', function (Blueprint $table) {
            // Re-add the unique index if you roll back
            $table->unique(['role', 'module'], 'uidx_role_module');
            $table->dropColumn([
                'assign_type',
                'assign_value',
                'recipient_signature_pattern'
            ]);
        });
    }
};
