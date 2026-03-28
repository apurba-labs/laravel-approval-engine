<?php

namespace ApurbaLabs\ApprovalEngine\Http\Controllers\Api;

use Illuminate\Routing\Controller;
use ApurbaLabs\ApprovalEngine\Services\WorkflowMetricsService;

class MetricsController extends Controller
{
    public function index(WorkflowMetricsService $service)
    {
        return response()->json([
            'data' => [
                'avg_approval_time' => $service->averageApprovalTime(),
                'sla_breaches' => $service->slaBreachCount(),
                'pending' => $service->pendingApprovals(),
            ]
        ]);
    }
}