<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('workflow_stages', function (Blueprint $table) {
            $table->string('assign_type')->default('role')->after('stage_order')->comment('Role, User, Permission');
            $table->string('assign_value')->nullable()->after('assign_type')->comment('The specific role, user ID, or permission name based on assign_type');
            $table->string('scope_field')->nullable()->after('assign_value')->comment('Field to scope the assignment');
            $table->string('name')->nullable()->after('scope_field')->comment('Optional name for the stage');
            $table->text('description')->nullable()->after('name')->comment('Description of the stage');
        });

        //Data Migration: Sync 'role' to new columns
        DB::table('workflow_stages')
            ->whereNotNull('role')
            ->update([
                'assign_value' => DB::raw('role'),
                'assign_type' => 'role'
            ]);
    }

    public function down(): void
    {
        Schema::table('workflow_stages', function (Blueprint $table) {
            $table->dropColumn(['assign_type', 'assign_value', 'scope_field', 'name', 'description']);
        });
    }
};