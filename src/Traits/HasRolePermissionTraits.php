<?php

namespace Fikrimastor\LaravelRolePermission\Traits;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;

trait HasRolePermissionTraits
{
    public function assignRole(...$roles)
    {
        $roles = $this->getAllRoles(...$roles);

        if (is_null($roles)) {
            return $this;
        }

        $this->roles()->sync($roles, false);
        $this->cacheRole();

        return $this->load('roles');
    }

    public function removeRole(...$roles)
    {
        $roles = $this->getAllRoles(...$roles);

        if (is_null($roles)) {
            return $this;
        }

        $this->roles()->detach($roles);
        $this->cacheRole();

        return $this->load('roles');
    }

    public function givePermissionsTo(...$permissions)
    {
        $permissions = $this->getAllPermissions(...$permissions);

        if (is_null($permissions)) {
            return $this;
        }

        $this->permissions()->sync($permissions, false);
        $this->cachePermissions();

        return $this->load('permissions');
    }

    public function withdrawPermissionsTo(...$permissions)
    {
        $permissions = $this->getAllPermissions(...$permissions);
        $this->permissions()->detach($permissions);
        $this->cachePermissions();

        return $this;
    }

    public function refreshPermissions(...$permissions)
    {
        $this->permissions()->detach();

        return $this->givePermissionsTo($permissions);
    }

    public function hasPermissionTo($permission)
    {
        return $this->hasPermissionThroughRole($permission) || $this->hasPermission($permission);
    }

    public function hasPermissionThroughRole($permission): bool
    {
        if (! ($permission instanceof Permission)) {
            if (is_string($permission)) {
                $permission = cache('permissions')->whereIn('name', [$permission])->first();
            } else {
                $permission = cache('permissions')->whereIn('name', $permission)->first();
            }
        }

        $roleKey = 'roles.' . auth()->id();
        foreach ($permission->roles as $role) {
            if (in_array($role->name, cache($roleKey))) {
                return true;
            }
        }

        return false;
    }

    public function hasRole(...$roles)
    {
        foreach ($roles as $role) {
            if ($this->roles->contains('name', $role)) {
                return true;
            }
        }

        return false;
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'role_user');
    }

    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'permission_user');
    }

    protected function hasPermission(...$permissions): bool
    {
        foreach ($permissions as $permission) {
            if ($this->permissions->contains('name', $permission)) {
                return true;
            }
        }

        return false;
    }

    protected function getAllPermissions(...$permissions)
    {
        $permissions = collect($permissions)->flatten();

        return Permission::whereIn('name', $permissions)->get();
    }

    protected function getAllRoles(...$roles)
    {
        $roles = collect($roles)->flatten();

        return Role::whereIn('name', $roles)->get();
    }

    private function cacheRole(): void
    {
        /** Clear cache for roles */
        $userId = auth()->id();
        $roleKey = "roles.{$userId}";
        cache()->forget($roleKey);
        cache()->remember($roleKey, 3600, fn () => $this->roles->pluck('name')->toArray());
    }

    private function cachePermissions(): void
    {
        /** Clear cache for permissions */
        cache()->forget('permissions');
        cache()->remember('permissions', 3600, fn () => Permission::with('roles')->get());
    }

    public function createPermissionData($permission)
    {
        $permission = Permission::create([
            'name' => $permission,
        ]);

        $this->cachePermissions();

        return $permission;
    }

    public function deletePermissionData(int $permission)
    {
        Permission::where('id', $permission)->delete();

        $this->cachePermissions();

        return cache('permissions');
    }

    public function createNewRoleData(string $roleName): Role
    {
        return Role::create(['name' => $roleName]);
    }

    public function deleteRoleData(int $role)
    {
        return Role::where('id', $role)->delete();
    }
}
