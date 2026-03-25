<?php

namespace App\Http\Controllers\API\V1;

use Illuminate\Http\Request;
use ApurbaLabs\ApprovalEngine\Models\WorkflowStage;
use ApurbaLabs\ApprovalEngine\Models\WorkflowRule;

class WorkflowConfigController extends Controller
{
    public function check($module)
    {
        $hasStage = WorkflowStage::where('module', $module)->exists();
        $hasRule = WorkflowRule::where('module', $module)->exists();

        return response()->json([
            'module' => $module,
            'has_stage' => $hasStage,
            'has_rule' => $hasRule,
        ]);
    }
}
