<?php
namespace ApurbaLabs\ApprovalEngine\Engine\Resolvers;

use ApurbaLabs\ApprovalEngine\Models\WorkflowRule;

class WorkflowRecipientResolver
{
    public function resolve(WorkflowRule $rule)
    {
        return match ($rule->assign_type) {
            'role' => $this->resolveByRole($rule->assign_value ?? $rule->role),
            'user' => $this->resolveByUserId($rule->assign_value),
            default => null,
        };
    }

    protected function resolveByRole(string $role)
    {
        $userModel = config('auth.providers.users.model');
        return $userModel::where('role', $role)->first();
    }

    protected function resolveByUserId($id)
    {
        $userModel = config('auth.providers.users.model');

        return $userModel::find($id);
    }
}