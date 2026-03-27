<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller; 

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
