<?php

namespace ApurbaLabs\ApprovalEngine\Engine;

use ApurbaLabs\ApprovalEngine\Contracts\WorkflowModuleInterface;

use ApurbaLabs\ApprovalEngine\Actions\ApproveBatchAction;
use ApurbaLabs\ApprovalEngine\Actions\FetchApprovedRecordsAction;
use ApurbaLabs\ApprovalEngine\Actions\MoveToNextStageAction;
use ApurbaLabs\ApprovalEngine\Events\WorkflowStarted;

use ApurbaLabs\ApprovalEngine\Models\WorkflowBatch;
use ApurbaLabs\ApprovalEngine\Models\WorkflowInstance;
use ApurbaLabs\ApprovalEngine\Models\WorkflowLog;

use ApurbaLabs\ApprovalEngine\Support\StageNavigator;

use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Str;
use RuntimeException;

class WorkflowEngine
{
    public function start($module, array $data): Collection
    {
        $moduleInstance = is_string($module)
            ? $this->getModule($module)
            : $module;

        $moduleName = $moduleInstance->name();
        //dump("Batch validation failed for: " . $moduleName);
        try {
            $moduleInstance->validate($data);

            $stageNavigator = app(StageNavigator::class);
            $firstStage = $stageNavigator->getFirstStage($moduleName);

            // create instance
            $workflow = WorkflowInstance::create([
                'module' => $moduleName,
                'current_stage_order' => $firstStage->stage_order,
                'status' => 'pending',
                'payload' => $data,
                'started_at' => now(),
            ]);

            // create log (metrics)
            WorkflowLog::create([
                'workflow_instance_id' => $workflow->id,
                'module' => $moduleName,
                'role' => $firstStage->role,
                'stage_order' => $firstStage->stage_order,
                'entered_at' => now(),
            ]);

            // event
            event(new WorkflowStarted(collect([$workflow])));
        
            return collect([$workflow]);

        } catch (\Exception $e) {

            dump("Batch validation failed for: " . $e);

            \Log::warning("Batch validation failed for {$moduleName}: " . $e->getMessage());
            return collect(); 
        }
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

    private function isWithinWindow($setting): bool
    {
        if (!$setting) return true;

        $now = now();

        return (!$setting->start_time || $now >= $setting->start_time)
            && (!$setting->end_time || $now <= $setting->end_time);
    }
}
