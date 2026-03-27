<?php
namespace ApurbaLabs\ApprovalEngine\Domains\WorkflowForm\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use ApurbaLabs\ApprovalEngine\Domains\WorkflowForm\Services\FormSchemaService;
use ApurbaLabs\ApprovalEngine\Domains\WorkflowForm\Services\FormSubmissionService;

class WorkflowFormController extends Controller
{
    public function show($module, FormSchemaService $schemaService)
    {
        $form = $schemaService->getActiveForm($module);

        return response()->json($form);
    }

    public function submit(
        Request $request,
        $module,
        FormSchemaService $schemaService,
        FormSubmissionService $submissionService
    ) {
        $form = $schemaService->getActiveForm($module);

        if (!$form) {
            return response()->json(['error' => 'Form not found'], 404);
        }
        
        $submission = $submissionService->submit($request->all(), $form);

        return response()->json($submission);
    }
}