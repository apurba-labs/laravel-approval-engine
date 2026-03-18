<?php

namespace ApurbaLabs\ApprovalEngine\Tests\Feature;

use ApurbaLabs\ApprovalEngine\Tests\TestCase;
use ApurbaLabs\ApprovalEngine\Models\WorkflowBatch;
use ApurbaLabs\ApprovalEngine\Engine\WorkflowEngine;
//use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\DatabaseTransactions; 
use ApurbaLabs\ApprovalEngine\Tests\Models\User;
use Mockery\MockInterface;

class ApprovalControllerTest extends TestCase
{
    use DatabaseTransactions;

    /** @test */
    public function it_can_approve_a_batch_via_token()
    {
        $batch = WorkflowBatch::create([
            'module' => 'requisition',
            'role' => 'HOSD',
            'token' => 'secure-token-123',
            'stage' => 1,
            'window_start' => now(),
            'window_end' => now(),
        ]);

        //Mock the WorkflowEngine to expect the approveBatch call
        $this->mock(WorkflowEngine::class, function (MockInterface $mock) use ($batch) {
            $mock->shouldReceive('approveBatch')
                ->once()
                ->with('secure-token-123', 1); // Token and UserID
        });
        $user = User::create([
                'name' => 'Apurba',
                'email' => 'apurba@example.com',
                'password' => bcrypt('password'),
            ]);
        
        $response = $this->actingAs($user)
            ->get("/approval/batch/{$batch->token}");

        $response->assertStatus(200)
            ->assertJson(['message' => 'Batch approved successfully']);
    }
}
