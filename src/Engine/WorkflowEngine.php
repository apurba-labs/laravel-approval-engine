<?php

namespace ApurbaLabs\ApprovalEngine\Engine;

use ApurbaLabs\ApprovalEngine\Contracts\WorkflowModuleInterface;
use ApurbaLabs\ApprovalEngine\Actions\ApproveBatchAction;
use ApurbaLabs\ApprovalEngine\Actions\FetchApprovedRecordsAction;
use ApurbaLabs\ApprovalEngine\Actions\MoveToNextStageAction;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;
use RuntimeException;

class WorkflowEngine
{
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

                //dump("Module Class: " . get_class($module));
                //dump("Expected Interface: ApurbaLabs\ApprovalEngine\Contracts\WorkflowModuleInterface");
                //dump("Does it implement it? " . ($module instanceof \ApurbaLabs\ApprovalEngine\Contracts\WorkflowModuleInterface ? 'YES' : 'NO'));

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
    public function getApprovedRecords(string $module, $start, $end): Collection
    {
        $moduleInstance = is_string($module) ? $this->getModule($module) : $module;

        return app(FetchApprovedRecordsAction::class)
            ->execute($moduleInstance, $start, $end);
    }

    public function approveBatch($token, $userId)
    {
        return app(ApproveBatchAction::class)
            ->execute($token, $userId);
    }
    protected function moveToNextStage($batch,$stage)
    {

        return app(MoveToNextStageAction::class)
            ->execute($token, $userId);

    }
}
