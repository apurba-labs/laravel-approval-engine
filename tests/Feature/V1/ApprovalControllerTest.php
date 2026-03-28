<?php

use ApurbaLabs\ApprovalEngine\Tests\Feature\V1;

use ApurbaLabs\ApprovalEngine\Tests\TestCase;
use ApurbaLabs\ApprovalEngine\Models\WorkflowBatch;
use ApurbaLabs\ApprovalEngine\Models\WorkflowStage;

use ApurbaLabs\ApprovalEngine\Engine\WorkflowEngine;
use ApurbaLabs\ApprovalEngine\Tests\Support\Models\User;
use ApurbaLabs\ApprovalEngine\Tests\Support\Models\Role;
use Mockery\MockInterface;

class ApprovalControllerTest extends TestCase
{

    /** @test 
     * @group v1
    */
    public function it_can_approve_a_batch_via_token()
    {
        
        $user = Role::where('name', 'HOSD')->first()?->users()->first() ?? User::factory()->withRole('HOSD')->create();
        $stage = WorkflowStage::factory()->forModule('requisition')->forRole('HOSD')->atStage(1)->create();
        $batch = WorkflowBatch::factory()->forModule('requisition')->forRole('HOSD')->withToken('secure-token-12345')->completed()->create();

        //Mock the WorkflowEngine to expect the approveBatch call
        $this->mock(WorkflowEngine::class, function (MockInterface $mock) use ($batch, $user) {
            $mock->shouldReceive('approveBatch')
                ->once()
                ->with('secure-token-12345', $user->id); // Token and UserID
        });
        //$role = Role::factory()->forName->('HOSD')->create();
        
        
        $response = $this->actingAs($user)
            ->get("/api/v1/batch/{$batch->token}");

        $response->assertStatus(200)
            ->assertJson(['message' => 'Batch approved successfully']);
    }
}
