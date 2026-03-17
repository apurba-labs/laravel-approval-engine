<?php

namespace ApurbaLabs\ApprovalEngine\Tests\Feature;

use ApurbaLabs\ApprovalEngine\Tests\TestCase; 
use Illuminate\Foundation\Testing\RefreshDatabase;

use ApurbaLabs\ApprovalEngine\Database\Seeders\WorkflowSeeder;
use ApurbaLabs\ApprovalEngine\Engine\WorkflowEngine;
use ApurbaLabs\ApprovalEngine\Contracts\WorkflowModuleInterface;
use ApurbaLabs\ApprovalEngine\Enums\WorkflowStatus;

use ApurbaLabs\ApprovalEngine\Models\WorkflowBatch;
use ApurbaLabs\ApprovalEngine\Tests\Models\User;
use ApurbaLabs\ApprovalEngine\Tests\Models\Requisition;

use ApurbaLabs\ApprovalEngine\Tests\Modules\RequisitionModule;

class WorkflowCommandTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->loadLaravelMigrations();
        $this->seed(WorkflowSeeder::class);
    }

    /** @test */
    public function workflow_command_runs_successfully()
    {
        try {
            $user = User::create([
                'name' => 'Apurba',
                'email' => 'apurba@example.com',
                'password' => bcrypt('password'),
            ]);
            //dump("User Created ID: " . $user->id);

            $req = Requisition::create([
                'user_id' => $user->id,
                'reference_id' => 'REQ-001',
                'stage' => 1,
                'status' => WorkflowStatus::APPROVED->value,
                'stage_status' => WorkflowStatus::APPROVED->value,
                'approved_at' => now(),
                'created_at' => now(),
            ]);
            //dump("Requisition Created ID: " . $req->id);
        } catch (\Exception $e) {
            // This will print the EXACT error (e.g., Table not found, or Mass Assignment)
            dd("Database Error: " . $e->getMessage()); 
        }

        // Use the command signature you defined in SendWorkflowBatchCommand
        $this->artisan('approval:send-batch')
             ->assertExitCode(0);

        $this->assertDatabaseHas('workflow_batches', [
            'status' => 'sent'
        ]);

        $batch = WorkflowBatch::first();
        
        $this->assertNotNull($batch);
        $this->assertNotNull($batch->token);
        $this->assertNotNull($batch->window_start);
        $this->assertNotNull($batch->window_end);
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


}
