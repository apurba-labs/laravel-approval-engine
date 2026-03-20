<?php

namespace ApurbaLabs\ApprovalEngine;

use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use ApurbaLabs\ApprovalEngine\Engine\WorkflowEngine;

class ApprovalEngine
{
    /**
     * Start a single workflow.
     * Accessible via: ApprovalEngine::start(...)
     */
    public static function start($module, array $data, bool $fireEvent = true)
    {
        try {
            $engine = app(WorkflowEngine::class);
            $result = $engine->start($module, $data);

            $response = [
                'status' => 'success',
                'module' => $module,
                'data'   => $result
            ];

            // Fire event for single start if requested
            if ($fireEvent) {
                event(new WorkflowStarted(collect([$response])));
            }

            return $response;

        } catch (Exception $e) {
            Log::error("Workflow failed for module {$module}: " . $e->getMessage());

            return [
                'status' => 'failed',
                'module' => $module,
                'error'  => $e->getMessage()
            ];
        }
    }

    /**
     * Start multiple workflows.
     * Accessible via: ApprovalEngine::startMultiple(...)
     */
    public static function startMultiple(array $modules, array $data): Collection
    {
        $results = collect();

        foreach ($modules as $module) {
            // We pass false to 'fireEvent' so we don't fire 10 events for 10 modules
            $results->push(self::start($module, $data, false));
        }

        // Filter only successes to pass to the final event
        $successes = $results->where('status', 'success');

        if ($successes->isNotEmpty()) {
            event(new WorkflowStarted($successes));
        }

        return $results;
    }
}
