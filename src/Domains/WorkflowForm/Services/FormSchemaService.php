<?php
namespace ApurbaLabs\ApprovalEngine\Domains\WorkflowForm\Services;

use ApurbaLabs\ApprovalEngine\Domains\WorkflowForm\Models\WorkflowForm;

class FormSchemaService
{
    public function getActiveForm(string $moduleSlug)
    {
        return WorkflowForm::whereHas('module', function ($q) use ($moduleSlug) {
                $q->where('slug', $moduleSlug);
            })
            ->where('is_active', true)
            ->latest()
            ->first();
    }
}