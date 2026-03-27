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
        Schema::create('workflow_forms', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('module_id');
            $table->integer('version')->default(1);

            $table->json('schema');
            $table->boolean('is_active')->default(true);

            $table->timestamps();

            $table->foreign('module_id')
                ->references('id')->on('workflow_modules')
                ->onDelete('cascade');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('workflow_forms');
    }
};
