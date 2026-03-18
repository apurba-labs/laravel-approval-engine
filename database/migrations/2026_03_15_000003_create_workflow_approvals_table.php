<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('workflow_approvals', function (Blueprint $table) {

            $table->id();

            $table->foreignId('batch_id')->constrained('workflow_batches');

            $table->unsignedBigInteger('user_id');

            $table->integer('stage');

            $table->string('status')->default('pending');

            $table->timestamp('approved_at')->nullable();

            $table->text('comments')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('workflow_approvals');
    }
};
