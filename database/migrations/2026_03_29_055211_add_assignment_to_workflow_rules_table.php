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
        Schema::table('workflow_rules', function (Blueprint $table) {
            if (!Schema::hasColumn('workflow_rules', 'assign_type')) {
                $table->string('assign_type')->default('role')
                      ->after('role')
                      ->comment('role, user, or custom_logic');
            }

            if (!Schema::hasColumn('workflow_rules', 'assign_value')) {
                $table->string('assign_value')->nullable()
                      ->after('assign_type')
                      ->comment('The specific role name or user ID');
            }

            $table->index(['module', 'assign_type'], 'idx_rules_assignment_lookup');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('workflow_rules', function (Blueprint $table) {
            $table->dropIndex('idx_rules_assignment_lookup');
            $table->dropColumn(['assign_type', 'assign_value']);
        });
    }
};
