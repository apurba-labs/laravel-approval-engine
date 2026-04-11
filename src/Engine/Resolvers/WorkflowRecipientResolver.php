<?php

namespace ApurbaLabs\ApprovalEngine\Engine\Resolvers;

use ApurbaLabs\IAM\Facades\IAM;

class WorkflowRecipientResolver
{
    public function resolve($stage, $context = null)
    {
        $assignType = $stage->resolved_assign_type
            ?? $stage->assign_type
            ?? 'role';

        $assignValue = $stage->resolved_assign_value
            ?? $stage->assign_value
            ?? $stage->role;

        $scopeId = $this->resolveScopeId($stage, $context);

        return match ($assignType) {
            'role'       => $this->resolveByRole($assignValue, $scopeId),
            'user'       => $this->resolveByUserId($assignValue),
            'permission' => $this->resolveByPermission($assignValue, $scopeId),
            default      => null,
        };
    }

    protected function resolveByRole(string $role, $scopeId = null)
    {
        return IAM::usersWithRole($role, $scopeId)->first();
    }

    protected function resolveByUserId($id)
    {
        $userModel = config('auth.providers.users.model');

        return $userModel::find($id);
    }

    protected function resolveByPermission(string $permission, $scopeId = null)
    {
        return IAM::usersWithPermission($permission, $scopeId)->first();
    }

    protected function resolveScopeId($stage, $context = null)
    {
        if (!$context) {
            return null;
        }

        $payloadScope = data_get($context, 'payload.scope_id');
        if ($payloadScope) {
            return $payloadScope;
        }

        if ($stage->scope_field) {
            return data_get($context, $stage->scope_field);
        }

        return null;
    }
}