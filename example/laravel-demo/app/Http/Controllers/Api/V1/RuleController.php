<?php
namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use ApurbaLabs\ApprovalEngine\ApprovalEngine;
use ApurbaLabs\ApprovalEngine\Models\WorkflowInstance;
use OpenApi\Attributes as OA;

class RuleController extends Controller
{
    #[OA\Get(
        path: '/api/v1/rules',
        summary: 'Get all rules',
        tags: ['Rules'],
        responses: [
            new OA\Response(response: 200, description: 'Success')
        ]
    )]
    public function index()
    {
        return WorkflowRule::latest()->get();
    }

    public function store(Request $request)
    {
        return WorkflowRule::create($request->all());
    }

    public function update(Request $request, $id)
    {
        $rule = WorkflowRule::findOrFail($id);
        $rule->update($request->all());

        return $rule;
    }

    public function destroy($id)
    {
        WorkflowRule::destroy($id);
        return response()->noContent();
    }
}