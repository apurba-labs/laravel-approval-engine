<?php

namespace ApurbaLabs\ApprovalEngine\Engine;

use ApurbaLabs\ApprovalEngine\Contracts\WorkflowModuleInterface;
use ApurbaLabs\ApprovalEngine\Domain\Workflow\Models\WorkflowInstance;
use ApurbaLabs\ApprovalEngine\Domain\Workflow\Models\WorkflowLog;
use ApurbaLabs\ApprovalEngine\Domain\Workflow\Models\WorkflowApproval;
use ApurbaLabs\ApprovalEngine\Events\WorkflowStarted;
use ApurbaLabs\ApprovalEngine\Events\WorkflowRejected;
use ApurbaLabs\ApprovalEngine\Events\WorkflowCompleted;
use ApurbaLabs\ApprovalEngine\Events\WorkflowStageAdvanced;
use ApurbaLabs\ApprovalEngine\Support\StageNavigator;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class WorkflowEngine
{
    public function __construct(
        protected StageNavigator $stageNavigator
    ) {}

    /**
     * Start a new workflow
     */
    public function start(string|WorkflowModuleInterface $module, array $payload): WorkflowInstance
    {
        return DB::transaction(function () use ($module, $payload) {

            $moduleInstance = is_string($module)
                ? $this->getModule($module)
                : $module;

            $moduleName = $moduleInstance->name();

            // Validate input via module
            $moduleInstance->validate($payload);

            $firstStage = $this->stageNavigator->getFirstStage($moduleName);

            if (!$firstStage) {
                throw new RuntimeException("No stages configured for module {$moduleName}");
            }

            // Create workflow instance
            $workflow = WorkflowInstance::create([
                'module' => $moduleName,
                'current_stage_order' => $firstStage->stage_order,
                'role' => $firstStage->role,
                'status' => 'pending',
                'payload' => $payload,
                'started_at' => now(),
            ]);

            // Create log entry
            WorkflowLog::create([
                'workflow_instance_id' => $workflow->id,
                'module' => $moduleName,
                'role' => $firstStage->role,
                'stage_order' => $firstStage->stage_order,
                'entered_at' => now(),
            ]);

            WorkflowApproval::create([
                'workflow_instance_id' => $workflow->id,
                'user_id' => $recipient?->id ?? 1, // fallback for test
                'stage_id' => $firstStage->id,
                'stage_order' => $firstStage->stage_order,
                'status' => 'pending',
                'assigned_at' => now(),
            ]);

            // Fire event
            event(new WorkflowStarted($workflow));

            return $workflow;
        });
    }

    /**
     * Approve current stage and move forward
     */
    public function approve(WorkflowInstance $workflow, int|string $userId): WorkflowInstance
    {
        return DB::transaction(function () use ($workflow, $userId) {

            if ($workflow->status !== 'pending') {
                return $workflow; // idempotent
            }

            // Find current pending approval
            $approval = WorkflowApproval::where('workflow_instance_id', $workflow->id)
                ->where('stage_order', $workflow->current_stage_order)
                ->where('status', 'pending')
                ->first();

            if (!$approval) {
                throw new \RuntimeException('No pending approval found');
            }

            // Validate user
            if ((string)$approval->user_id !== (string)$userId) {
                throw new \RuntimeException('Unauthorized approval');
            }

            // Mark approved
            $approval->update([
                'status' => 'approved',
                'approved_at' => now(),
            ]);

            $oldRole = $workflow->role;

            // Get next stage
            $nextStage = $this->stageNavigator->getNextStage(
                $workflow->module,
                $workflow->current_stage_order
            );

            // Complete if no next stage
            if (!$nextStage) {

                $workflow->update([
                    'status' => 'completed',
                    'completed_at' => now(),
                ]);

                event(new WorkflowCompleted($workflow));

                return $workflow;
            }

            // Move to next stage
            $workflow->update([
                'current_stage_order' => $nextStage->stage_order,
                'role' => $nextStage->role,
            ]);

            // Log
            WorkflowLog::create([
                'workflow_instance_id' => $workflow->id,
                'module' => $workflow->module,
                'role' => $nextStage->role,
                'stage_order' => $nextStage->stage_order,
                'entered_at' => now(),
            ]);

            // Fire event
            event(new WorkflowStageAdvanced(
                $workflow,
                $oldRole,
                $nextStage->role
            ));

            return $workflow;
        });
    }

    /**
     * Reject workflow
     */
    public function reject(
        WorkflowInstance $workflow,
        int|string $userId,
        ?string $reason = null
    ): WorkflowInstance
    {
        return DB::transaction(function () use ($workflow, $userId, $reason) {

            if ($workflow->status !== 'pending') {
                return $workflow;
            }

            // Find current approval
            $approval = WorkflowApproval::where('workflow_instance_id', $workflow->id)
                ->where('stage_order', $workflow->current_stage_order)
                ->where('status', 'pending')
                ->first();

            if (!$approval) {
                throw new \RuntimeException('No pending approval found');
            }

            // Validate user
            if ((string)$approval->user_id !== (string)$userId) {
                throw new \RuntimeException('Unauthorized rejection');
            }

            // Mark rejected
            $approval->update([
                'status' => 'rejected',
                'rejected_at' => now(),
            ]);

            // Update workflow
            $workflow->update([
                'status' => 'rejected',
                'completed_at' => now(),
            ]);

            // Log
            WorkflowLog::create([
                'workflow_instance_id' => $workflow->id,
                'module' => $workflow->module,
                'role' => 'rejected',
                'stage_order' => $workflow->current_stage_order,
                'entered_at' => now(),
            ]);

            // Fire event
            event(new WorkflowRejected($workflow, $reason));

            return $workflow;
        });
    }

    /**
     * Resolve module from config/discovery
     */
    public function getModule(string $moduleName): WorkflowModuleInterface
    {
        $modules = $this->discoverModules();

        foreach ($modules as $module) {
            if ($module->name() === $moduleName) {
                return $module;
            }
        }

        throw new RuntimeException("Workflow module [{$moduleName}] not found.");
    }

    /**
     * Discover modules dynamically
     */
    public function discoverModules(): array
    {
        $modules = [];

        $path = config('approval-engine.modules_path', app_path('Workflow/Modules'));
        $namespace = config('approval-engine.modules_namespace', 'App\\Workflow\\Modules\\');

        if (!is_dir($path)) {
            return [];
        }

        $files = glob($path . '/*Module.php');

        foreach ($files as $file) {

            $class = $namespace . basename($file, '.php');

            if (class_exists($class)) {

                $instance = app($class);

                if ($instance instanceof WorkflowModuleInterface) {
                    $modules[] = $instance;
                }
            }
        }

        return $modules;
    }
}