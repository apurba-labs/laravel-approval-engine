<?php

namespace ApurbaLabs\ApprovalEngine\Tests\Feature\V14;

use ApurbaLabs\ApprovalEngine\Tests\TestCase;
use Carbon\Carbon;

use ApurbaLabs\ApprovalEngine\Models\WorkflowApproval;
use ApurbaLabs\ApprovalEngine\Models\WorkflowInstance;
use ApurbaLabs\ApprovalEngine\Models\WorkflowNotification;
use ApurbaLabs\ApprovalEngine\Models\WorkflowStage;

use ApurbaLabs\ApprovalEngine\Tests\Support\Traits\InteractsWithIAM;

class WorkflowIAMIntegrationTest extends TestCase
{
    use InteractsWithIAM;
    /** @test 
     * @group v1.4
    */
    public function it_resolves_user_by_permission_assignment()
    {
        $user = $this->createUserWithPermission(
            'finance',
            null,
            ['approval.finance.approve']
        );

        WorkflowStage::factory()->create([
            'module' => 'expense',
            'stage_order' => 1,
            'assign_type' => 'permission',
            'assign_value' => 'approval.finance.approve',
        ]);

        $workflow = WorkflowInstance::create([
            'module' => 'expense',
            'payload' => [],
            'current_stage_order' => 1,
            'status' => 'pending',
        ]);

        $recipient = app(\ApurbaLabs\ApprovalEngine\Engine\Resolvers\WorkflowRecipientResolver::class)
            ->resolve(WorkflowStage::first(), $workflow);

        $this->assertEquals($user->id, $recipient->id);
    }
    /** @test 
    * @group v1.4
    */
    public function it_resolves_direct_user_assignment()
    {
        $userModel = config('auth.providers.users.model');
        $user = $userModel::factory()->create();

        WorkflowStage::factory()->create([
            'module' => 'expense',
            'stage_order' => 1,
            'assign_type' => 'user',
            'assign_value' => $user->id,
        ]);

        $workflow = WorkflowInstance::create([
            'module' => 'expense',
            'payload' => [],
            'current_stage_order' => 1,
            'status' => 'pending',
        ]);

        $recipient = app(\ApurbaLabs\ApprovalEngine\Engine\Resolvers\WorkflowRecipientResolver::class)
            ->resolve(WorkflowStage::first(), $workflow);

        $this->assertEquals($user->id, $recipient->id);
    }

    /** @test 
     * @group v1.4
    */
    public function it_resolves_user_with_scope_based_permission()
    {
        $user = $this->createUserWithPermission(
            'finance',
            505,
            ['approval.finance.approve']
        );

        WorkflowStage::factory()->create([
            'module' => 'expense',
            'stage_order' => 1,
            'assign_type' => 'permission',
            'assign_value' => 'approval.finance.approve',
            'scope_field' => 'payload.scope_id',
        ]);

        $workflow = WorkflowInstance::create([
            'module' => 'expense',
            'payload' => [
                'scope_id' => 505,
            ],
            'current_stage_order' => 1,
            'status' => 'pending',
        ]);

        $recipient = app(\ApurbaLabs\ApprovalEngine\Engine\Resolvers\WorkflowRecipientResolver::class)
            ->resolve(WorkflowStage::first(), $workflow);

        $this->assertEquals($user->id, $recipient->id);
    }

    /** @test 
     * @group v1.4
    */
    public function it_returns_null_if_no_recipient_found()
    {
        WorkflowStage::factory()->create([
            'module' => 'expense',
            'stage_order' => 1,
            'assign_type' => 'role',
            'assign_value' => 'non_existing_role',
        ]);

        $workflow = WorkflowInstance::create([
            'module' => 'expense',
            'payload' => [],
            'current_stage_order' => 1,
            'status' => 'pending',
        ]);

        $recipient = app(\ApurbaLabs\ApprovalEngine\Engine\Resolvers\WorkflowRecipientResolver::class)
            ->resolve(WorkflowStage::first(), $workflow);

        $this->assertNull($recipient);
    }
}