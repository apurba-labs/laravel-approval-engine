<?php

namespace ApurbaLabs\ApprovalEngine\Tests\Feature\V1;

use ApurbaLabs\ApprovalEngine\Tests\TestCase; 

use ApurbaLabs\ApprovalEngine\Database\Seeders\WorkflowDatabaseSeeder;
use ApurbaLabs\ApprovalEngine\Engine\WorkflowEngine;
use ApurbaLabs\ApprovalEngine\Contracts\WorkflowModuleInterface;
use ApurbaLabs\ApprovalEngine\Enums\WorkflowStatus;


use ApurbaLabs\ApprovalEngine\Models\WorkflowBatch;
use ApurbaLabs\ApprovalEngine\Models\WorkflowSetting;
use ApurbaLabs\ApprovalEngine\Models\WorkflowNotification;
use ApurbaLabs\ApprovalEngine\Models\WorkflowInstance;

use ApurbaLabs\ApprovalEngine\Tests\Support\Models\User;
use ApurbaLabs\ApprovalEngine\Tests\Support\Models\Role;
use ApurbaLabs\ApprovalEngine\Tests\Support\Models\Requisition;

use ApurbaLabs\ApprovalEngine\Tests\Support\Modules\RequisitionModule;

use Illuminate\Support\Facades\Mail;
use ApurbaLabs\ApprovalEngine\Mail\BatchApprovalMail;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use ApurbaLabs\ApprovalEngine\Tests\Support\Traits\InteractsWithIAM;

class WorkflowCommandTest extends TestCase
{
    use InteractsWithIAM;

    /** @test 
     * @group v1
    */
    public function workflow_command_runs_successfully()
    {

        // Set Time
        $testTime =Carbon::create(2026, 3, 16, 10, 0, 0, 'Asia/Dhaka');
        $this->travelTo($testTime);

        $user = $this->createUserWithPermission('hosd');

        // Create THE ONLY setting in the DB
        $setting = WorkflowSetting::factory()->create([
            'module' => 'requisition',
            'role' => 'hosd',
            'frequency' => 'daily',
            'is_active' => 1,
            'send_time' => '09:00:00',
            'assign_type' => 'role',
            'assign_value' => 'hosd',
        ]);

        // Create THE ONLY notification
        WorkflowNotification::factory()->create([
            'module' => 'requisition',
            'role' => 'hosd',
            'is_sent' => 0,
            'batch_id' => null,
            'assign_type' => 'role',
            'assign_value' => 'hosd',
            'recipient_signature' => 'role:hosd', 
            'recipient_id' => $user->id,
            'recipient_type' => get_class($user),
        ]);

        // Act
        $this->artisan('approval:send-batch');

        // Assert
        $this->assertDatabaseHas('workflow_batches', [
            'module' => 'requisition',
            'role' => 'hosd',
        ]);
    }



    /** @test 
     * @group v1
    */
    public function test_make_workflow_module_command()
    {
        $this->artisan('make:workflow-module TestModule')
            ->assertExitCode(0);
    }

    /** @test 
     * @group v1
    */
    public function it_can_discover_modules_automatically()
    {
        $engine = app(WorkflowEngine::class);

        $modules = $engine->discoverModules();

         $this->assertNotEmpty($modules, 'The modules array should not be empty');

        $this->assertInstanceOf(WorkflowModuleInterface::class, $modules[0]);
        
    }
    /** @test 
     * @group v1
    */
    public function it_can_display_user_names_from_relationship()
    {

        WorkflowSetting::factory()
                ->forModule('requisition')
                ->forRole('HOSD')
                ->atFrequency('weekly', 1) 
                ->atSendTime('09:00:00')
                ->create();


        $user = Role::where('name', 'HOSD')->first()?->users()->first() ?? User::factory()->withRole('HOSD')->create();

        $req = Requisition::create([
            'user_id' => $user->id,
            'reference_id' => 'REQ-001',
            'stage' => 1,
            'status' => WorkflowStatus::APPROVED->value,
            'stage_status' => WorkflowStatus::APPROVED->value,
            'approved_at' => now(),
            'created_at' => now(),
        ]);

        $engine = app(WorkflowEngine::class);
        $module = new RequisitionModule();
        
        $records = $engine->getApprovedRecords('requisition', now()->subDay(), now()->addDay());
         
        $this->assertNotNull($records->first()->user, 'User relationship is NULL');
        //dump("requisition collection: " . $records->first());
        $this->assertEquals($user->name, data_get($records->first(), 'user.name'));
    }

    /** @test 
     * @group v1
    */
    public function it_only_runs_weekly_batches_on_the_correct_day()
    {
        // Setup: Monday is 1. March 16, 2026 is a MONDAY.
        $mondayDate = Carbon::create(2026, 3, 16, 10, 0, 0, 'Asia/Dhaka');
        $sundayDate = Carbon::create(2026, 3, 15, 10, 0, 0, 'Asia/Dhaka');

        $user = $this->createUserWithPermission('hosd');
        // 1. Create the Setting (Weekly on Monday)
        WorkflowSetting::factory()
                ->forModule('requisition')
                ->forRole('hosd')
                ->atFrequency('weekly', 1) 
                ->atSendTime('09:00:00')
                ->create();

        // --- TEST SUNDAY: Should NOT send ---
        $this->travelTo($sundayDate);
        
        // Create a pending notification for Sunday
        WorkflowNotification::factory()->forModule('requisition')->forRole('hosd')->create([
            'is_sent' => false,
            'batch_id' => null,
            'assign_type' => 'role',
            'assign_value' => 'hosd',
            'recipient_signature' => 'role:hosd', 
            'recipient_id' => $user->id,
            'recipient_type' => get_class($user),
        ]);

        $this->artisan('approval:send-batch');
        
        $this->assertDatabaseMissing('workflow_batches', [
            'module' => 'requisition',
            'role' => 'hosd'
        ]);

        // --- TEST MONDAY: SHOULD send ---
        $this->travelTo($mondayDate);

        // Create another pending notification for Monday
        WorkflowNotification::factory()->forModule('requisition')->forRole('hosd')->create([
            'is_sent' => false,
            'batch_id' => null,
            'assign_type' => 'role',
            'assign_value' => 'hosd',
            'recipient_signature' => 'role:hosd', 
            'recipient_id' => $user->id,
            'recipient_type' => get_class($user),
        ]);

        $this->artisan('approval:send-batch');
        
        $this->assertDatabaseHas('workflow_batches', [
            'module' => 'requisition',
            'role' => 'hosd'
        ]);
    }

    /** @test 
     * @group v1
    */

    public function workflow_command_creates_batch_and_sends_notifications()
    {
        ///$this->withoutExceptionHandling();

        Notification::fake(); // use Notification instead of Log

        // Fixed time (Monday 10 AM)
        $testTime = now()->next('Monday')->setHour(10);
        $this->travelTo($testTime);

        $user = $this->createUserWithPermission('hosd');

        // Setting
        WorkflowSetting::factory()
            ->forModule('requisition')
            ->forRole('hosd')
            ->atFrequency('weekly', 1)
            ->atSendTime('09:00:00')
            ->create([
                'assign_type' => 'role',
                'assign_value' => 'hosd',
            ]);

        // Instance
        $instance = WorkflowInstance::factory()->create([
            'module' => 'requisition',
            'status' => 'pending',
            'current_stage_order'=>1,
            'payload' => [
                'user_id' => $user->id
            ]
        ]);

        // Notification (IMPORTANT)
        WorkflowNotification::factory()->create([
            'workflow_instance_id' => $instance->id,
            'module' => 'requisition',
            'role' => 'hosd',
            'status' => 'pending',
            'recipient_id' => $user->id,
            'recipient_type' => User::class,
            'recipient_signature' => 'role:hosd',
            'created_at' => now()->subMinutes(5),
        ]);

        // Act
        $this->artisan('approval:send-batch')->assertExitCode(0);

        // Assert Notification Sent
        Notification::assertSentTo(
            $user,
            \ApurbaLabs\ApprovalEngine\Notifications\WorkflowBatchNotification::class
        );

        // Assert Batch Created
        $this->assertDatabaseHas('workflow_batches', [
            'module' => 'requisition',
            'role' => 'hosd'
        ]);

        // Assert Notifications Updated
        $this->assertDatabaseHas('workflow_notifications', [
            'status' => 'sent'
        ]);
    }

}
