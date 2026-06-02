<?php

namespace ApurbaLabs\ApprovalEngine\Engine;

use ApurbaLabs\ApprovalEngine\Services\ModuleRegistry;
use ApurbaLabs\ApprovalEngine\Engine\Resolvers\WorkflowRecipientResolver;
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

            $hash = hash('sha256', json_encode([
                'module' => $module,
                'payload' => $payload,
            ]));
            $existing = WorkflowInstance::where('payload_hash', $hash)->first();
            if ($existing) {
                 return $existing; // idempotent
            }
            // Create workflow instance
            $workflow = WorkflowInstance::create([
                'module' => $moduleName,
                'current_stage_order' => $firstStage->stage_order,
                'role' => $firstStage->role,
                'status' => 'pending',
                'payload' => $payload,
                'payload_hash' => $hash,
                'started_at' => now(),
            ]);

            $recipient = app(WorkflowRecipientResolver::class)
                ->resolve($firstStage, $workflow);

            if (!$recipient) {
                throw new \RuntimeException(
                    "No recipient resolved for stage [{$firstStage->id}]"
                );
            }

            // Create log entry
            WorkflowLog::create([
                'workflow_instance_id' => $workflow->id,
                'module' => $moduleName,
                'user_id' => $recipient->id,
                'role' => $firstStage->role,
                'stage_order' => $firstStage->stage_order,
                'entered_at' => now(),
            ]);

            WorkflowApproval::create([
                'workflow_instance_id' => $workflow->id,
                'user_id' => $recipient->id,
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

                $this->closeCurrentStageLog($workflow);

                $workflow->update([
                    'status' => 'completed',
                    'completed_at' => now(),
                ]);

                event(new WorkflowCompleted($workflow));

                return $workflow;
            }

            $recipient = app(WorkflowRecipientResolver::class)
                ->resolve($nextStage, $workflow);

            if (!$recipient) {
                throw new \RuntimeException(
                    "No recipient resolved for next stage [{$nextStage->id}]"
                );
            }

            // Close current log
            $this->closeCurrentStageLog($workflow);

            // Move to next stage
            $workflow->update([
                'user_id' => $recipient->id,
                'stage_id' => $nextStage->id,
                'stage_order' => $nextStage->stage_order,
                'current_stage_order' => $nextStage->stage_order,
                'role' => $nextStage->role,
            ]);

            // Log
            WorkflowLog::create([
                'workflow_instance_id' => $workflow->id,
                'module' => $workflow->module,
                'user_id' => $recipient->id,
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

            $this->closeCurrentStageLog($workflow);

            // Update workflow
            $workflow->update([
                'status' => 'rejected',
                'completed_at' => now(),
            ]);

            // Log
            WorkflowLog::create([
                'workflow_instance_id' => $workflow->id,
                'module' => $workflow->module,
                'user_id' => $userId,
                'role' => 'rejected',
                'stage_order' => $workflow->current_stage_order,
                'entered_at' => now(),
            ]);

            // Fire event
            event(new WorkflowRejected($workflow, $reason));

            return $workflow;
        });
    }

    protected function closeCurrentStageLog( WorkflowInstance $workflow ): void
    {
        WorkflowLog::query()
            ->where('workflow_instance_id', $workflow->id)
            ->where('stage_order', $workflow->current_stage_order)
            ->whereNull('exited_at')
            ->latest('id')
            ->first()
            ?->update([
                'exited_at' => now(),
            ]);
    }

    /**
     * Resolve module from config/discovery
     */
    public function getModule(string $moduleName): WorkflowModuleInterface
    {
        $module = app(ModuleRegistry::class)
            ->get($moduleName);

        if (!$module) {
            throw new RuntimeException(
                "Workflow module [{$moduleName}] not found."
            );
        }

        return $module;
    }
}