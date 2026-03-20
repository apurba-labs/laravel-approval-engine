<?php

namespace ApurbaLabs\ApprovalEngine\Engine;

use ApurbaLabs\ApprovalEngine\Contracts\WorkflowModuleInterface;
use ApurbaLabs\ApprovalEngine\Actions\ApproveBatchAction;
use ApurbaLabs\ApprovalEngine\Actions\FetchApprovedRecordsAction;
use ApurbaLabs\ApprovalEngine\Actions\MoveToNextStageAction;
use ApurbaLabs\ApprovalEngine\Events\WorkflowStarted;
use ApurbaLabs\ApprovalEngine\Models\WorkflowBatch;

use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Str;
use RuntimeException;

class WorkflowEngine
{
    public function start($module, array $data): Collection
    {
        // Resolve module instance
        $moduleInstance = is_string($module) ? $this->getModule($module) : $module;

        // Create workflow batch
        $workflow = new WorkflowBatch();
        $workflow->module = $moduleInstance->name();

        // Fill dynamic data (amount, total_amount, etc.)
        foreach ($data as $key => $value) {
            $workflow->$key = $value;
        }

        $workflow->current_stage_order = 1; // initial stage
        $workflow->save();

        // Resolve dynamic stages (V1.2)
        $resolver = app(config('approval-engine.stage_resolver'));
        $stages = $resolver->resolve($workflow, $workflow->stages_array ?? []);

        $workflow->stages_array = $stages;
        $workflow->save();

        // Fire event (single workflow)
        event(new WorkflowStarted($workflow));

        return $workflow;
    }
    /**
     * Resolve the module class from config and ensure it implements the interface.
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
     * Engine can find all modules automatically
     */
    public function discoverModules(): array
    {
        $modules = [];
        $path = config('approval-engine.modules_path', app_path('Workflow/Modules'));
        $namespace = config('approval-engine.modules_namespace', 'App\\Workflow\\Modules\\');

        if (!is_dir($path)) return [];

        $files = glob($path . '/*Module.php');

        foreach ($files as $file) {

            $class = $namespace . basename($file, '.php');

            if (class_exists($class)) {

                $module = app($class);

                if ($module instanceof WorkflowModuleInterface) {
                    $modules[] = $module;
                }

            }
        }

        return $modules;
    }

    /**
     * Get records that have completed the approval process.
     */
    public function getApprovedRecords(string $module, $start, $end): EloquentCollection
    {
        $moduleInstance = is_string($module) ? $this->getModule($module) : $module;

        return app(FetchApprovedRecordsAction::class)
            ->execute($moduleInstance, $start, $end);
    }

    public function approveBatch($token, $userId): EloquentCollection
    {
        return app(ApproveBatchAction::class)
            ->execute($token, $userId);
    }
    protected function moveToNextStage($batch,$stage): EloquentCollection 
    {

        return app(MoveToNextStageAction::class)
            ->execute($token, $userId);

    }
}
