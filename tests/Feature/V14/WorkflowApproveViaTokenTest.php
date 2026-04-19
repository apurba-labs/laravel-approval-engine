<?php

namespace ApurbaLabs\ApprovalEngine\Tests\Feature\V14;

use ApurbaLabs\ApprovalEngine\Tests\TestCase;

use ApurbaLabs\ApprovalEngine\Services\WorkflowManager;
use ApurbaLabs\ApprovalEngine\Services\ApprovalTokenService;
use ApurbaLabs\ApprovalEngine\Domain\Workflow\Models\WorkflowStage;
use ApurbaLabs\ApprovalEngine\Domain\Workflow\Models\WorkflowApproval;

use ApurbaLabs\ApprovalEngine\Tests\Support\Traits\InteractsWithIAM;

class WorkflowApproveViaTokenTest extends TestCase
{
    use InteractsWithIAM;

    protected function setUp(): void
    {
        parent::setUp();

        $this->setupWorkflowEnvironment(); 
    }

    /** @test */
    public function it_can_approve_via_token()
    {
        $manager = app(WorkflowManager::class);

        $workflow = $manager->start('requisition', [
            'amount' => 5000,
        ]);

        $approval = WorkflowApproval::where('workflow_instance_id', $workflow->id)
            ->where('status', 'pending')
            ->first();

        $token = app(ApprovalTokenService::class)
            ->create($workflow, $approval->user_id);

        $this->post('/api/v1/approvals/token/approve', [
            'token' => $token->token,
        ]);


        $updated = \DB::table('approval_tokens')
            ->where('id', $token->id)
            ->first();

        $this->assertNotNull($updated->used_at);

        $this->assertDatabaseHas('approval_tokens', [
            'id' => $token->id
        ]);

        $this->assertNotNull(
            \DB::table('approval_tokens')
                ->where('id', $token->id)
                ->value('used_at')
        );
    }
}