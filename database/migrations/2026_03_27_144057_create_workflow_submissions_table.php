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
        Schema::create('workflow_submissions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('form_id');
            $table->unsignedBigInteger('workflow_instance_id')->nullable();

            $table->json('data');

            $table->string('status')->default('pending');

            $table->timestamps();

            $table->foreign('form_id')
                ->references('id')->on('workflow_forms')
                ->onDelete('cascade');

            $table->foreign('workflow_instance_id')
                ->references('id')->on('workflow_instances')
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('workflow_submissions');
    }
};
