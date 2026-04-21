<?php

namespace ApurbaLabs\ApprovalEngine\Tests\Support\Traits;

use Illuminate\Support\Str;
use ApurbaLabs\IAM\Models\Role;
use ApurbaLabs\IAM\Models\Permission;
use ApurbaLabs\ApprovalEngine\Domain\Workflow\Models\WorkflowStage;

trait InteractsWithIAM
{
    /**
     * Create user + role + permissions + assign role
     */
    protected function createUserWithRole(
        string $roleName,
        array $permissions = [],
        $scopeId = null
    ) {
        $userModel = config('auth.providers.users.model')
            ?? \ApurbaLabs\ApprovalEngine\Tests\Support\Models\User::class;

        $user = $userModel::factory()->create();

        // Create role
        $role = Role::firstOrCreate(
            ['name' => $roleName],
            ['slug' => Str::slug($roleName)]
        );

        // Attach permissions
        foreach ($permissions as $pName) {
            $permission = Permission::firstOrCreate(
                ['slug' => $pName],
                [
                    'name' => Str::headline(str_replace('.', ' ', $pName)),
                    'resource' => explode('.', $pName)[0] ?? '*',
                    'action' => explode('.', $pName)[1] ?? '*',
                ]
            );

            $role->permissions()->syncWithoutDetaching([$permission->id]);
        }

        // Assign role
        if (method_exists($user, 'assignRole')) {
            $user->assignRole($role, $scopeId);
        }

        return $user;
    }

    /**
     * Create user with direct permission assignment
     */
    protected function createUserWithPermission(string $roleName, $scopeId = null, array $permissions = [])
    {
        return $this->createUserWithRole($roleName, $permissions, $scopeId);
    }

    /**
     * Create workflow stages for module
     */
    protected function createWorkflowStages(string $module = 'requisition'): void
    {
        $stages = [
            [
                'module' => $module,
                'stage_order' => 1,
                'role' => 'manager',
                'assign_type' => 'role',
                'assign_value' => 'manager',
            ],
            [
                'module' => $module,
                'stage_order' => 2,
                'role' => 'finance',
                'assign_type' => 'role',
                'assign_value' => 'finance',
            ],
        ];

        foreach ($stages as $stage) {
            WorkflowStage::create($stage);
        }
    }

    /**
     * FULL SETUP → READY FOR TEST
     */
    protected function setupWorkflowEnvironment(): array
    {
        // Create users with roles
        $manager = $this->createUserWithRole('manager');
        $finance = $this->createUserWithRole('finance');

        // Create stages
        $this->createWorkflowStages('requisition');

        return [
            'manager' => $manager,
            'finance' => $finance,
        ];
    }
}