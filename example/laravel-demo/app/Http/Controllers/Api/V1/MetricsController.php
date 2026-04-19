<?php
namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use ApurbaLabs\ApprovalEngine\ApprovalEngine;
use ApurbaLabs\ApprovalEngine\Domain\Workflow\Models\WorkflowInstance;

class MetricsController extends Controller
{
    public function overview()
    {
        return [
            'avg_time' => app(MetricsService::class)->avgApprovalTime('requisition'),
            'bottlenecks' => app(MetricsService::class)->bottlenecks('requisition'),
        ];
    }
}