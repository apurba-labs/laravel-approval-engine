<?php

namespace ApurbaLabs\ApprovalEngine\Tests\Support\Traits;

use ApurbaLabs\IAM\Models\Role;
use ApurbaLabs\IAM\Models\Permission;
use Illuminate\Support\Str;

trait InteractsWithIAM
{
    /**
     * Helper to create a user and assign a role with permissions.
     */
    protected function createUserWithPermission(string $roleName, $scopeId = null, array $permissions = [])
    {
        // Fallback to your test User model if config isn't set
        $userModel = config('auth.providers.users.model') ?? \ApurbaLabs\ApprovalEngine\Tests\Support\Models\User::class;
        
        $user = $userModel::create([
            'name'     => 'Apurba Test User',
            'email'    => 'test_' . bin2hex(random_bytes(4)) . '@example.com',
            'password' => bcrypt('password'),
        ]);

        // Create/Get Role
        $role = Role::firstOrCreate(
            ['slug' => Str::slug($roleName)],
            ['name' => $roleName]
        );

        // Create & Sync Permissions
        foreach ($permissions as $pName) {
            $parts = explode('.', $pName);
            $resource = $parts[0] ?? '*';
            $action   = $parts[1] ?? '*';

            $permission = Permission::firstOrCreate(
                ['slug' => $pName],
                [
                    'name' => Str::headline(str_replace('.', ' ', $pName)),
                    'resource' => $resource,
                    'action'   => $action,
                ]
            );

            $role->permissions()->syncWithoutDetaching([$permission->id]);
        }

        // Assign Role to User
        if (method_exists($user, 'assignRole')) {
            $user->assignRole($role, $scopeId);
        }

        return $user;
    }
}
