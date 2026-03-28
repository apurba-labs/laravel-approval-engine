<?php
namespace ApurbaLabs\ApprovalEngine\Tests\Feature\V14;

use ApurbaLabs\ApprovalEngine\Tests\TestCase;

use ApurbaLabs\ApprovalEngine\Domains\WorkflowForm\Models\WorkflowForm;
use ApurbaLabs\ApprovalEngine\Domains\WorkflowForm\Models\WorkflowModule;
use ApurbaLabs\ApprovalEngine\Models\WorkflowRule;
use ApurbaLabs\ApprovalEngine\Models\WorkflowInstance;
use ApurbaLabs\ApprovalEngine\Models\WorkflowApproval;
use ApurbaLabs\ApprovalEngine\Models\WorkflowStage;
use ApurbaLabs\ApprovalEngine\Models\WorkflowSetting;

use ApurbaLabs\ApprovalEngine\Tests\Support\Models\User;
use ApurbaLabs\ApprovalEngine\Tests\Support\Models\Role;

class WorkflowFormOrchestrationTest extends TestCase
{

    /** @test 
     * @group v1.4
    */
    public function it_executes_full_workflow_from_form_submission()
    {
        // Create User (finance_manager)
        $user = User::factory()->forName('finance manager')->withRole('finance_manager')->create();

        WorkflowStage::factory()->create([
            'module' => 'expense',
            'stage_order' => 1,
            'role' => 'finance_manager',
        ]);
        // Create Module
        $module = WorkflowModule::firstOrCreate(
            ['slug' => 'expense'],
            ['name' => 'Expense Approval']
        );

        // Create Form
        $form = WorkflowForm::factory()
            ->for($module, 'workflowModule') 
            ->create([
                'is_active' => true,
            ]);
            
        // Access the module from the form object if needed
        $module = $form->workflowModule;
        // Create Rule
        WorkflowRule::factory()
            ->forModule($module->slug)
            ->forField('amount')
            ->withOperator('>')
            ->withValue(10000)
            ->targetRole('finance_manager')
            ->create();

        WorkflowSetting::factory()->create([
            'module' => $module->slug,
            'role' => 'finance_manager',
            'frequency' => 'instant', // IMPORTANT
            'is_active' => 1,
            'send_time' => '00:00:00'
        ]);
        // Call API
        $response = $this->postJson('/api/v1/workflow/forms/expense', [
            'amount' => 15000,
        ]);

        $response->assertStatus(200);

        // Assert WorkflowInstance created
        $this->assertDatabaseHas('workflow_instances', [
            'module' => 'expense',
        ]);

        $workflow = WorkflowInstance::first();

        // Assert Approval created
        $this->assertDatabaseHas('workflow_approvals', [
            'workflow_instance_id' => $workflow->id,
            'user_id' => $user->id,
        ]);

        // Assert status pending
        $approval = WorkflowApproval::first();
        $this->assertEquals('pending', $approval->status);
    }
}