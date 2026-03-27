<?php
namespace ApurbaLabs\ApprovalEngine\Domains\WorkflowForm\Services;

use ApurbaLabs\ApprovalEngine\Domains\WorkflowForm\Models\WorkflowSubmission;
use ApurbaLabs\ApprovalEngine\Domains\WorkflowForm\Models\WorkflowForm;

class FormSubmissionService
{
    public function submit(array $data, WorkflowForm $form)
    {
        return WorkflowSubmission::create([
            'form_id' => $form->id,
            'data' => $data,
            'status' => 'pending',
        ]);
    }
}