<?php
namespace ApurbaLabs\ApprovalEngine\Engine\Resolvers;

use ApurbaLabs\ApprovalEngine\Models\WorkflowRule;

class WorkflowRecipientResolver
{
    public function resolve(WorkflowRule $rule)
    {
        return $this->resolveByRole($rule->role);
    }

    protected function resolveByRole(string $roleName)
    {
        $userModel = config('auth.providers.users.model');
        return $userModel::whereHas('role', function ($query) use ($roleName) {
            $query->where('name', $roleName);
        })->first();
    }
}