<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use ApurbaLabs\ApprovalEngine\Enums\WorkflowStatus;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('requisitions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->index();
            $table->string('reference_id')->nullable();
            $table->decimal('total_amount', 15, 2)->default(0.0);
            $table->string('type', 10)->default('new_inject');
            $table->mediumInteger('stage')->unsigned()->default(1);
            $table->string('stage_status', 15)->default(WorkflowStatus::PENDING->value);
            $table->string('status', 15)->default(WorkflowStatus::PROCESSING->value);
            $table->dateTime('approved_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('requisitions');
    }
};
