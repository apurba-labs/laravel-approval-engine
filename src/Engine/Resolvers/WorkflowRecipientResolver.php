<?php
namespace ApurbaLabs\ApprovalEngine\Engine\Resolvers;

use ApurbaLabs\ApprovalEngine\Models\WorkflowRule;
use ApurbaLabs\IAM\Facades\IAM;

// This class is responsible for resolving the recipient of a workflow task based on the assignment type and value defined in the workflow rule.
class WorkflowRecipientResolver
{
    // The resolve method determines the recipient based on the stage's assignment type and value, with support for dynamic resolution based on workflow rules.
    public function resolve($stage, $context = null)
    {
        $assignType = $stage->resolved_assign_type
            ?? $stage->assign_type
            ?? 'role';

        $assignValue = $stage->resolved_assign_value
            ?? $stage->assign_value
            ?? $stage->role;

        return match ($assignType) {
            'role'       => $this->resolveByRole($assignValue),
            'user'       => $this->resolveByUserId($assignValue),
            'permission' => $this->resolveByPermission(
                $assignValue,
                $this->resolveScopeId($stage, $context)
            ),
            default => null,
        };
    }
    // This method assumes that the user model has a 'role' attribute. Adjust as necessary for your application's structure.
    protected function resolveByRole(string $role)
    {
        return IAM::usersWithRole($role)->first();
    }

    // This method retrieves a user by their ID. Adjust as necessary for your application's structure.
    protected function resolveByUserId($id)
    {
        $userModel = config('auth.providers.users.model');

        return $userModel::find($id);
    }

    // This method uses the IAM facade to find users with the specified permission and scope, and returns the first matching user. Adjust as necessary for your application's structure and requirements.
    protected function resolveByPermission(string $permission, $scopeId = null)
    {
        return IAM::usersWithPermission($permission, $scopeId)->first();
    }
    
    // This method resolves the scope ID for permission checks based on the stage's scope field and the provided context. It returns null if no scope field is defined or if the context is not provided.
    protected function resolveScopeId($stage, $context = null)
    {
        if (!$stage->scope_field || !$context) {
            return null;
        }

        return data_get($context, $stage->scope_field);
    }
}