<?php
namespace ApurbaLabs\ApprovalEngine\Domains\WorkflowForm\Services;

use ApurbaLabs\ApprovalEngine\Engine\WorkflowEngine;
use ApurbaLabs\ApprovalEngine\Domains\WorkflowForm\Models\WorkflowSubmission;
use ApurbaLabs\ApprovalEngine\Domains\WorkflowForm\Models\WorkflowForm;
use ApurbaLabs\ApprovalEngine\Actions\StartWorkflowAction;

class FormSubmissionService
{
    public function submit(array $data, WorkflowForm $form)
    {
        if (!$form->workflowModule) {
            throw new \RuntimeException("Form module not linked");
        }
        // Save submission
        $submission = WorkflowSubmission::create([
            'form_id' => $form->id,
            'data' => $data,
            'status' => 'pending',
        ]);

        // Start workflow
        $workflow = app(WorkflowEngine::class)
            ->start($form->workflowModule->slug, $data);


        // Link submission → workflow
        $submission->update([
            'workflow_instance_id' => $workflow->id,
        ]);

        return $submission;
    }
}