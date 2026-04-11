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
        Schema::table('workflow_batches', function (Blueprint $table) {
            // Assignment snapshot
            $table->string('assign_type')->nullable()->after('role');
            $table->string('assign_value', 255)->nullable()->after('assign_type');

            // Deterministic batching / auth signature
            $table->string('recipient_signature', 255)->nullable()->after('assign_value');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('workflow_batches', function (Blueprint $table) {
            $table->dropColumn([
                'assign_type',
                'assign_value',
                'recipient_signature'
            ]);
        });
    }
};
