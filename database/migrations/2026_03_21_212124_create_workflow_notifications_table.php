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
            $table->string('module');
            $table->string('role');

            // This creates recipient_id and recipient_type
            $table->nullableMorphs('recipient'); 

            // Link to the Instance (Source of Truth)
            $table->unsignedBigInteger('workflow_instance_id');

            // Link to the Batch (Only filled when grouped for Daily/Weekly)
            $table->unsignedBigInteger('batch_id')->nullable();

            $table->boolean('is_sent')->default(false);
            $table->timestamp('sent_at')->nullable();

            $table->timestamps();

            // Foreign Key Constraints for Data Integrity
            $table->foreign('workflow_instance_id')
                  ->references('id')->on('workflow_instances')
                  ->onDelete('cascade');
            
            $table->foreign('batch_id')
                  ->references('id')->on('workflow_batches')
                  ->onDelete('set null');

            // Index for the Cron Job to find unsent items quickly
            $table->index(['module', 'role', 'is_sent']);
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
