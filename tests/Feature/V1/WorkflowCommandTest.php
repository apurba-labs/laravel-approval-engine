<?php

namespace ApurbaLabs\ApprovalEngine\Tests\Feature\V1;

use ApurbaLabs\ApprovalEngine\Tests\TestCase; 

use ApurbaLabs\ApprovalEngine\Database\Seeders\WorkflowDatabaseSeeder;
use ApurbaLabs\ApprovalEngine\Engine\WorkflowEngine;
use ApurbaLabs\ApprovalEngine\Contracts\WorkflowModuleInterface;
use ApurbaLabs\ApprovalEngine\Enums\WorkflowStatus;


use ApurbaLabs\ApprovalEngine\Models\WorkflowBatch;
use ApurbaLabs\ApprovalEngine\Models\WorkflowSetting;

use ApurbaLabs\ApprovalEngine\Tests\Support\Models\User;
use ApurbaLabs\ApprovalEngine\Tests\Support\Models\Role;
use ApurbaLabs\ApprovalEngine\Tests\Support\Models\Requisition;

use ApurbaLabs\ApprovalEngine\Tests\Support\Modules\RequisitionModule;

use Illuminate\Support\Facades\Mail;
use ApurbaLabs\ApprovalEngine\Mail\BatchApprovalMail;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;


class WorkflowCommandTest extends TestCase
{
    /** @test 
     * @group v1
    */
    public function workflow_command_runs_successfully()
    {
        // 1. Set a fixed Monday for the test (e.g., March 16, 2026 is a Monday)
        $testTime = Carbon::create(2026, 3, 16, 10, 0, 0, 'Asia/Dhaka');
        $this->travelTo($testTime);
        
        try {
            // Ensure the setting matches the Day and Time we travelled to
            WorkflowSetting::factory()
                ->forModule('requisition')
                ->forRole('HOSD')
                ->atSendTime('09:00:00')
                ->atFrequency('weekly', 1) 
                ->create();

            $user = Role::where('name', 'HOSD')->first()?->users()->first() ?? User::factory()->withRole('HOSD')->create();

            // Create the record approved WITHIN the weekly window
            // The window will be roughly March 9th to March 16th.
            Requisition::create([
                'user_id' => $user->id,
                'reference_id' => 'REQ-001',
                'stage' => 1,
                'status' => WorkflowStatus::APPROVED->value,
                'stage_status' => WorkflowStatus::APPROVED->value,
                'approved_at' => $testTime->copy()->subHours(2), // Approved at 8:00 AM today
                'created_at' => $testTime->copy()->subDay(),
            ]);

        } catch (\Exception $e) {
            dd("Database Error: " . $e->getMessage()); 
        }

        // 4. Run the command
        $this->artisan('approval:send-batch')
            ->assertExitCode(0);

        // 5. Assertions
        $this->assertDatabaseHas('workflow_batches', [
            'module' => 'requisition',
            'status' => 'sent'
        ]);

        $batch = WorkflowBatch::first();
        $this->assertNotNull($batch);
        $this->assertNotNull($batch->token);
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
        $this->assertEquals('Apurba', data_get($records->first(), 'user.name'));
    }

    /** @test 
     * @group v1
    */
    public function it_only_runs_weekly_batches_on_the_correct_day()
    {
        // Setup the Setting once (Monday, 09:00 AM)
        WorkflowSetting::factory()
                ->forModule('requisition')
                ->forRole('HOSD')
                ->atFrequency('weekly', 1) 
                ->atSendTime('09:00:00')
                ->create();

        $user = Role::where('name', 'HOSD')->first()?->users()->first() ?? User::factory()->withRole('HOSD')->create();

        // Test Sunday: Should NOT create a batch for 'HOSD'
        $sunday = now()->startOfWeek()->subDay()->setHour(10);
        $this->travelTo($sunday);

        // Create a record approved just before this Sunday
        Requisition::create([
            'user_id' => $user->id,
            'reference_id' => 'REQ-SUN',
            'status' => WorkflowStatus::APPROVED->value,
            'approved_at' => $sunday->copy()->subHour(),
        ]);

        $this->artisan('approval:send-batch');
        // Assert specifically for 'HOSD'
        $this->assertDatabaseMissing('workflow_batches', [
            'module' => 'requisition',
            'role' => 'HOSD'
        ]);


        //Test Monday: SHOULD create a batch for 'HOSD'
        // We travel to Monday 10:00 AM
        $monday = now()->startOfWeek()->setHour(10);
        $this->travelTo($monday);

        // Create a record approved just before this Monday
        Requisition::create([
            'user_id' => $user->id,
            'reference_id' => 'REQ-MON',
            'status' => WorkflowStatus::APPROVED->value,
            'approved_at' => $monday->copy()->subHour(),
        ]);

        $this->artisan('approval:send-batch');
        
        $this->assertDatabaseHas('workflow_batches', [
            'module' => 'requisition',
            'role' => 'HOSD'
        ]);
    }

    /** @test 
     * @group v1
    */
    public function workflow_command_creates_batch_and_sends_email()
    {
        $this->withoutExceptionHandling(); 
        //Fake the Mailer
        Mail::fake();

        // Set fixed time and prepare Data
        $testTime = Carbon::create(2026, 3, 16, 10, 0, 0, 'Asia/Dhaka');
        $this->travelTo($testTime);

        $user = Role::where('name', 'HOSD')->first()?->users()->first() ?? User::factory()->withRole('HOSD')->create();

        // Create an approved requisition within the daily window
        Requisition::create([
            'user_id' => $user->id,
            'reference_id' => 'REQ-TEST-101',
            'status' => 'approved',
            'approved_at' => $testTime->copy()->subHour(),
        ]);

        // Ensure the setting is Active and due to run
        WorkflowSetting::factory()
            ->forModule('requisition')
            ->forRole('HOSD')
            ->atFrequency('weekly', 1) 
            ->atSendTime('09:00:00')
            ->create();

        $this->artisan('approval:send-batch')->assertExitCode(0);

        
        Mail::assertSent(BatchApprovalMail::class);

        // 5. If the above passes, check the recipient count
        Mail::assertSent(BatchApprovalMail::class, function ($mail) {
            return $mail->hasTo('apurbansingh@yahoo.com');
        });
    }

}
