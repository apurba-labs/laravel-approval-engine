<?php
namespace ApurbaLabs\ApprovalEngine\Tests\Feature\V14;

use ApurbaLabs\ApprovalEngine\Tests\TestCase;
use Illuminate\Support\Facades\Notification;

use ApurbaLabs\ApprovalEngine\Domains\WorkflowForm\Models\WorkflowForm;
use ApurbaLabs\ApprovalEngine\Domains\WorkflowForm\Models\WorkflowModule;
use ApurbaLabs\ApprovalEngine\Models\WorkflowRule;
use ApurbaLabs\ApprovalEngine\Models\WorkflowInstance;
use ApurbaLabs\ApprovalEngine\Models\WorkflowApproval;
use ApurbaLabs\ApprovalEngine\Models\WorkflowStage;
use ApurbaLabs\ApprovalEngine\Models\WorkflowSetting;



use ApurbaLabs\ApprovalEngine\Tests\Support\Traits\InteractsWithIAM;

class WorkflowFormOrchestrationTest extends TestCase
{
    use InteractsWithIAM;
    /** @test
     * @group v1.4
     */
    public function it_executes_full_workflow_from_form_submission()
    {
        Notification::fake();
        $roleName = 'finance-manager';
        $user = $this->createUserWithPermission($roleName);

        WorkflowStage::factory()->create([
            'module' => 'expense',
            'stage_order' => 1,
            'role' => 'finance_manager',
            'assign_type' => 'role',
            'assign_value' => 'finance-manager',
        ]);

        $module = WorkflowModule::firstOrCreate(
            ['slug' => 'expense'],
            ['name' => 'Expense Approval']
        );

        $form = WorkflowForm::factory()
            ->for($module, 'workflowModule')
            ->create([
                'is_active' => true,
            ]);

        WorkflowRule::factory()
            ->forModule($module->slug)
            ->forField('amount')
            ->withOperator('>')
            ->withValue(10000)
            ->targetRole('finance_manager')
            ->create([
                'assign_type' => 'role',
                'assign_value' => 'finance-manager',
            ]);

        WorkflowSetting::factory()->create([
            'module' => $module->slug,
            'role' => 'finance_manager',
            'frequency' => 'instant',
            'is_active' => 1,
            'send_time' => '00:00:00',
            'assign_type' => 'role',
            'assign_value' => 'finance-manager',
        ]);

        $response = $this->postJson('/api/v1/workflow/forms/expense', [
            'amount' => 15000,
        ]);

        $response->assertStatus(200);

        $workflow = WorkflowInstance::first();

        $this->assertNotNull($workflow);

        $this->assertDatabaseHas('workflow_instances', [
            'id' => $workflow->id,
            'module' => 'expense',
            'status' => 'pending',
        ]);

        $this->assertDatabaseHas('workflow_approvals', [
            'workflow_instance_id' => $workflow->id,
            'stage_order' => 1,
            'user_id' => $user->id,
            'status' => 'pending',
        ]);

        $this->assertDatabaseHas('workflow_notifications', [
            'workflow_instance_id' => $workflow->id,
            'role' => 'finance_manager',
            'assign_type' => 'role',
            'assign_value' => 'finance-manager',
            'recipient_id' => $user->id,
            'status' => 'sent',
        ]);

        
    }
}