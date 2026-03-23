<?php

namespace ApurbaLabs\ApprovalEngine\Tests\Feature;

use ApurbaLabs\ApprovalEngine\Tests\TestCase;
use ApurbaLabs\ApprovalEngine\Models\WorkflowBatch;
use Illuminate\Foundation\Testing\RefreshDatabase;
//use Illuminate\Foundation\Testing\DatabaseTransactions; 
use Illuminate\Support\Facades\Schema;

class BatchCreationTest extends TestCase
{
    use RefreshDatabase; // Handles migration and transactions automatically

    /** @test */
    public function test_batch_table_exists()
    {
        $this->assertTrue(
            Schema::hasTable('workflow_batches')
        );
    }
    /** @test */
    public function test_batch_is_created()
    {
        $batch = WorkflowBatch::create([
            'module' => 'requisition',
            'role' => 'HOSD',
            'stage' => 1,
            'token' => 'testtoken',
            'window_start' => now(),
            'window_end' => now()
        ]);

        $this->assertDatabaseHas('workflow_batches', [
            'token' => 'testtoken',
            'role' => 'HOSD'
        ]);
    }
}
