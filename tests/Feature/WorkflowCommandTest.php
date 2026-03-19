<?php

namespace ApurbaLabs\ApprovalEngine\Tests\Feature;

use ApurbaLabs\ApprovalEngine\Tests\TestCase; 
//use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\DatabaseTransactions; 

use ApurbaLabs\ApprovalEngine\Database\Seeders\WorkflowSeeder;
use ApurbaLabs\ApprovalEngine\Engine\WorkflowEngine;
use ApurbaLabs\ApprovalEngine\Contracts\WorkflowModuleInterface;
use ApurbaLabs\ApprovalEngine\Enums\WorkflowStatus;


use ApurbaLabs\ApprovalEngine\Models\WorkflowBatch;
use ApurbaLabs\ApprovalEngine\Tests\Models\User;
use ApurbaLabs\ApprovalEngine\Tests\Models\Requisition;

use ApurbaLabs\ApprovalEngine\Tests\Modules\RequisitionModule;

use Illuminate\Support\Facades\Mail;
use ApurbaLabs\ApprovalEngine\Mail\BatchApprovalMail;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;


class WorkflowCommandTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        $this->loadLaravelMigrations();
        $this->seed(WorkflowSeeder::class);
    }

    /** @test */
    public function workflow_command_runs_successfully()
    {
        // 1. Set a fixed Monday for the test (e.g., March 16, 2026 is a Monday)
        $testTime = Carbon::create(2026, 3, 16, 10, 0, 0, 'Asia/Dhaka');
        $this->travelTo($testTime);

        try {
            // 2. Ensure the setting matches the Day and Time we travelled to
            \DB::table('workflow_settings')
                ->updateOrInsert(
                    ['role' => 'HOSD', 'module' => 'requisition'],
                    [
                        'frequency' => 'weekly',
                        'weekly_day' => 1, // Monday
                        'send_time' => '09:00:00', // 09:00 is before our 10:00 travel time
                        'is_active' => true,
                        'last_run_at' => null, // Ensure it hasn't run yet
                        'timezone' => 'Asia/Dhaka'
                    ]
                );

            $user = User::create([
                'name' => 'Apurba',
                'email' => 'apurba@example.com',
                'password' => bcrypt('password'),
            ]);

            // 3. Create the record approved WITHIN the weekly window
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

    /** @test */
    public function test_make_workflow_module_command()
    {
        $this->artisan('make:workflow-module TestModule')
            ->assertExitCode(0);
    }

    /** @test */
    public function it_can_discover_modules_automatically()
    {
        $engine = app(WorkflowEngine::class);

        $modules = $engine->discoverModules();

         $this->assertNotEmpty($modules, 'The modules array should not be empty');

        $this->assertInstanceOf(WorkflowModuleInterface::class, $modules[0]);
        
    }
    /** @test */
    public function it_can_display_user_names_from_relationship()
    {
        DB::table('workflow_settings')
            ->where('role', 'HOSD')
            ->where('module', 'requisition')
            ->update([
                'frequency' => 'weekly',
                'weekly_day' => 1, // Monday
                'send_time' => '09:00:00',
                'is_active' => true,
            ]);

        $user = User::create([
                'name' => 'Apurba',
                'email' => 'apurba@example.com',
                'password' => bcrypt('password'),
            ]);

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

    /** @test */
    public function it_only_runs_weekly_batches_on_the_correct_day()
    {
        // Setup the Setting once (Monday, 09:00 AM)
        DB::table('workflow_settings')->updateOrInsert(
            ['role' => 'HOSD', 'module' => 'requisition'],
            [
                'frequency' => 'weekly',
                'weekly_day' => 1, // Monday
                'send_time' => '09:00:00',
                'is_active' => true,
                'last_run_at' => null,
                'timezone' => 'Asia/Dhaka'
            ]
        );

        $user = User::create([
            'name' => 'Apurba', 'email' => 'a@test.com', 'password' => 'pass'
        ]);

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

    /** @test */
    public function workflow_command_creates_batch_and_sends_email()
    {
        $this->withoutExceptionHandling(); 
        //Fake the Mailer
        Mail::fake();

        // Set fixed time and prepare Data
        $testTime = Carbon::create(2026, 3, 16, 10, 0, 0, 'Asia/Dhaka');
        $this->travelTo($testTime);

        $user = User::create([
            'name' => 'Apurba',
            'email' => 'apurbansingh@yahoo.com',
            'password' => bcrypt('password'),
        ]);

        // Create an approved requisition within the daily window
        Requisition::create([
            'user_id' => $user->id,
            'reference_id' => 'REQ-TEST-101',
            'status' => 'approved',
            'approved_at' => $testTime->copy()->subHour(),
        ]);

        // Ensure the setting is Active and due to run
        DB::table('workflow_settings')->updateOrInsert(
            ['module' => 'requisition', 'role' => 'HOSD'],
            [
                'frequency' => 'daily',
                'send_time' => '09:00:00',
                'is_active' => true,
                'last_run_at' => null,
                'timezone' => 'Asia/Dhaka'
            ]
        );

        $this->artisan('approval:send-batch')->assertExitCode(0);

        Mail::assertSent(BatchApprovalMail::class);

        // 5. If the above passes, check the recipient count
        Mail::assertSent(BatchApprovalMail::class, function ($mail) {
            return $mail->hasTo('apurbansingh@yahoo.com');
        });
    }

}
