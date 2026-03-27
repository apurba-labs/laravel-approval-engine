<?php
namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use ApurbaLabs\ApprovalEngine\ApprovalEngine;
use ApurbaLabs\ApprovalEngine\Models\WorkflowInstance;

use Illuminate\Support\Facades\DB;
use OpenApi\Attributes as OA;

class WorkflowController extends Controller
{
    public function index()
    {
        return WorkflowInstance::latest()->paginate();
    }

    #[OA\Post(
        path: '/api/v1/workflows',
        summary: 'Start a workflow (Dynamic)',
        description: 'Initiates a workflow. Fields like "type" and "reference_id" apply only to Requisitions, while "total_amount" applies to both.',
        tags: ['Workflows'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['module', 'total_amount'],
                properties: [
                    // Shared Fields
                    new OA\Property(property: 'module', type: 'string', enum: ['requisition', 'purchase'], example: 'purchase'),
                    new OA\Property(property: 'total_amount', type: 'number', format: 'float', example: 5000.50),
                    
                    // Requisition Specific Fields
                    new OA\Property(property: 'type', type: 'string', description: 'REQUISITION ONLY: Type of inject', example: 'new_inject', nullable: true),
                    new OA\Property(property: 'reference_id', type: 'string', description: 'REQUISITION ONLY: Unique reference', example: 'REQ-9901', nullable: true),
                    
                    // Purchase Specific Fields (if any others exist)
                    new OA\Property(property: 'description', type: 'string', description: 'Optional description for the process', nullable: true),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201, 
                description: 'Success',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'Purchase created and workflow initiated.'),
                        new OA\Property(property: 'data', type: 'object', description: 'The created model (Purchase or Requisition)'),
                        new OA\Property(property: 'workflow', type: 'object', description: 'Approval Engine status')
                    ]
                )
            ),
            new OA\Response(response: 422, description: 'Validation error or invalid module')
        ]
    )]
    public function store(Request $request)
    {
        $modelMap = [
            'requisition' => \App\Models\Requisition::class,
            'purchase'    => \App\Models\Purchase::class,
        ];

        $module = $request->input('module');
        if (!isset($modelMap[$module])) {
            return response()->json(['error' => 'Invalid module'], 422);
        }

        $modelClass = $modelMap[$module];
        
        // Get all input
        $data = $request->all();
        $data['user_id'] = auth()->id()?? 1;
        $data['status'] = $data['status'] ?? 'pending';

        $data['approved_at'] = now();

        // Logic for module-specific fields
        if ($module === 'purchase') {
            $data['created_by'] = auth()->id()?? 1;
            // Remove requisition-only fields so Purchase doesn't crash
            $payload = collect($data)->only(['total_amount', 'user_id', 'created_by', 'status'])->toArray();
        } else {
            $data['reference_id'] = $data['reference_id'] ?? 'REQ-' . time();
            $data['type'] = $data['type'] ?? 'NEW';
            $data['stage'] = $data['stage'] ?? 1;
            // Include requisition fields
            $payload = collect($data)->only(['total_amount', 'user_id', 'reference_id', 'type', 'stage', 'status'])->toArray();
        }

        try {
            return DB::transaction(function () use ($module, $modelClass, $payload) {
                $record = $modelClass::create($payload);

                $workflow = ApprovalEngine::start($module, $payload);

                return response()->json([
                    'message' => 'Process started',
                    'data' => $record,
                    'workflow' => $workflow
                ], 201);
            });
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }


    public function show($id)
    {
        return WorkflowInstance::findOrFail($id);
    }

    public function approve($id)
    {
        return app(\ApurbaLabs\ApprovalEngine\Engine\WorkflowEngine::class)
            ->approve($id);
    }

    public function reject($id)
    {
        // Todo
    }
}