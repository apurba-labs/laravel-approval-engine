<?php

namespace ApurbaLabs\ApprovalEngine;

use ApurbaLabs\ApprovalEngine\Engine\WorkflowEngine;

class ApprovalEngine
{
    public static function start($module, $data)
    {
        return app(WorkflowEngine::class)->start($module, $data);
    }

    public static function startMultiple(array $modules, $data)
    {
        $engine = app(WorkflowEngine::class);

        $results = [];
        foreach ($modules as $module) {
            $results[] = $engine->start($module, $data);
        }

        return $results;
    }
}
