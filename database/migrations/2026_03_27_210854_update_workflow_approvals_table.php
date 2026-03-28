<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('workflow_approvals', function (Blueprint $table) {
            if (!Schema::hasColumn('workflow_approvals', 'workflow_instance_id')) {
                $table->unsignedBigInteger('workflow_instance_id')->after('id');
            }

            // DROP the old stage column
            if (Schema::hasColumn('workflow_approvals', 'stage')) {
                $table->dropColumn('stage');
            }

            if (!Schema::hasColumn('workflow_approvals', 'stage_id')) {
                $table->unsignedBigInteger('stage_id')->nullable()->after('workflow_instance_id');
                $table->integer('stage_order')->nullable()->after('stage_id');
            }

            // Change batch_id to nullable
            $table->unsignedBigInteger('batch_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('workflow_approvals', function (Blueprint $table) {
            // Before making it NOT NULL again, we must fill NULLs with 0 or a dummy ID 
            // but in a migration, it's safer to just drop the added columns first.
            $table->dropColumn(['workflow_instance_id', 'stage_id', 'stage_order']);
            
            if (!Schema::hasColumn('workflow_approvals', 'stage')) {
                $table->integer('stage')->nullable();
            }

            // FIX: Don't force NOT NULL in down() if there's any chance of NULL data
            $table->unsignedBigInteger('batch_id')->nullable()->change();
        });
    }
};
