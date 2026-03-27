<?php
namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use ApurbaLabs\ApprovalEngine\ApprovalEngine;
use ApurbaLabs\ApprovalEngine\Models\WorkflowInstance;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        return WorkflowNotification::query()
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->when($request->module, fn($q) => $q->where('module', $request->module))
            ->latest()
            ->paginate(20);
    }

    public function show($id)
    {
        return WorkflowNotification::findOrFail($id);
    }
}