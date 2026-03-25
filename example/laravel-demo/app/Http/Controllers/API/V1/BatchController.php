<?php
namespace App\Http\Controllers\API\V1;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use ApurbaLabs\ApprovalEngine\ApprovalEngine;
use ApurbaLabs\ApprovalEngine\Models\WorkflowInstance;

class BatchController extends Controller
{
    public function index()
    {
        return WorkflowBatch::latest()->paginate();
    }

    public function run()
    {
        \Artisan::call('approval:send-batch --force');

        return response()->json([
            'message' => 'Batch executed successfully'
        ]);
    }
}