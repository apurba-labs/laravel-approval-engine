<?php

namespace ApurbaLabs\ApprovalEngine\Tests\Feature\V1;

use ApurbaLabs\ApprovalEngine\Tests\TestCase;
use ApurbaLabs\ApprovalEngine\Models\WorkflowBatch;
use ApurbaLabs\ApprovalEngine\Models\WorkflowStage;
use Illuminate\Support\Facades\Schema;

class BatchCreationTest extends TestCase
{
    /** @test 
     * @group v1
    */
    public function test_batch_table_exists()
    {
        $this->assertTrue(
            Schema::hasTable('workflow_batches')
        );
    }
    /** @test 
     * @group v1
    */
    public function test_batch_is_created()
    {
        $stage = WorkflowStage::factory()->forModule('requisition')->forRole('HOSD')->atStage(1)->create();
        $batch = WorkflowBatch::factory()->forModule('requisition')->withToken('testtoken')->forRole('HOSD')->completed()->create();

        $this->assertDatabaseHas('workflow_batches', [
            'token' => 'testtoken',
            'role' => 'HOSD'
        ]);
    }
}
