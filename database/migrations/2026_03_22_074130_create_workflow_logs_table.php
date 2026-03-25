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
        Schema::create('workflow_logs', function (Blueprint $table) {
             $table->id();

            // Link to the Instance
            $table->foreignId('workflow_instance_id')
                ->constrained('workflow_instances')
                ->onDelete('cascade');

            $table->string('module');
            $table->string('role');
            $table->integer('stage_order');

            // Who acted on this stage (null if just entered)
            $table->unsignedBigInteger('user_id')->nullable(); 

            $table->timestamp('entered_at');
            $table->timestamp('exited_at')->nullable(); // Set when moving to NEXT stage

            $table->timestamps();
            
            // Index for Timeline queries
            $table->index(['workflow_instance_id', 'stage_order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('workflow_logs');
    }
};
