<?php
namespace ApurbaLabs\ApprovalEngine\Engine\Resolvers;

use ApurbaLabs\ApprovalEngine\Models\WorkflowRule;

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
        $userModel = config('auth.providers.users.model');
        // Legacy fallback support: if the role is not found in the user model, we can return null or throw an exception based on your application's needs.
        return $userModel::where('role', $role)->first();
    }

    // This method retrieves a user by their ID. Adjust as necessary for your application's structure.
    protected function resolveByUserId($id)
    {
        $userModel = config('auth.providers.users.model');

        return $userModel::find($id);
    }

    // This method assumes that the permission can be checked using the `can` method on the user model.
    protected function resolveByPermission(string $permission, $scopeId = null)
    {
        $userModel = config('auth.providers.users.model');

        return $userModel::query()
            ->get()
            ->first(function ($user) use ($permission, $scopeId) {
                return $user->can($permission, $scopeId);
            });
    }
    // This method resolves the scope ID for permission checks based on the stage's scope field and the provided context. It returns null if the scope field is not defined or if the context is not provided.
    protected function resolveScopeId($stage, $context = null)
    {
        if (!property_exists($stage, 'scope_field') && !isset($stage->scope_field) ) {
            return null;
        }

        if (!$stage->scope_field || !$context) {
            return null;
        }

        return data_get($context, $stage->scope_field);
    }
}