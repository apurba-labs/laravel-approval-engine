<?php

namespace ApurbaLabs\ApprovalEngine\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use ApurbaLabs\ApprovalEngine\Engine\WorkflowEngine;

class ApprovalController extends Controller
{
    protected $engine;

    public function __construct(WorkflowEngine $engine)
    {
        $this->engine = $engine;
    }

    public function approve($token, Request $request)
    {
        $userId = $request->user()?->id ?? 0; // fallback if guest

        $this->engine->approveBatch($token, $userId);

        return response()->json([
            'message' => 'Batch approved successfully'
        ]);
    }
}
