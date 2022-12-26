<?php

namespace Fikrimastor\LaravelRolePermission;

use Fikrimastor\LaravelRolePermission\Models\Permission;
use Illuminate\Cache\CacheManager;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Gate;

class RolePermissionRegistrar
{
    protected $cache;
    protected CacheManager $cacheManager;

    public static $cacheExpirationTime;

    protected $roleClass;

    protected $permissionClass;

    public static string $rolePivot;

    public static string $permissionPivot;

    public static string $cacheKey;

    protected Collection $permissions;

    public function __construct(CacheManager $cacheManager)
    {
        $this->roleClass = config('permission.models.role');

        $this->permissionClass = config('permission.models.permission');

        $this->cacheManager = $cacheManager;

        $this->initializeCache();
    }

    public function initializeCache()
    {
        self::$cacheExpirationTime = config('permission.cache.expiration_time') ?: \DateInterval::createFromDateString('24 hours');

        self::$cacheKey = config('permission.cache.key');

        self::$rolePivot = config('permission.column_names.role_pivot_key') ?: 'role_id';

        self::$permissionPivot = config('permission.column_names.permission_pivot_key') ?: 'permission_id';

        $this->cache = $this->getCacheStoreFromConfig();
    }

    protected function getCacheStoreFromConfig()
    {
        $cacheDriver = config('permission.cache.store', 'default');

        if ($cacheDriver === 'default') {
            return $this->cacheManager->store();
        }

        if (! array_key_exists($cacheDriver, config('cache.stores'))) {
            $cacheDriver = 'array';
        }

        return $this->cacheManager->store($cacheDriver);
    }

    public function registerPermissions(): bool
    {
        $this->loadPermissions();

        $this->permissions->map(fn ($permission) => Gate::define($permission->name, function (User $user) use ($permission) {
            return $user->hasPermissionTo($permission) ?: null;
        }));

        return true;
    }

    private function loadPermissions(): void
    {
        if ($this->permissions) {
            return;
        }

        $this->permissions = $this->cache->remember(self::$cacheKey, self::$cacheExpirationTime, fn () => $this->getPermissionClass()->with('roles')->get());
    }

    public function getPermissionClass(): Permission
    {
        return app($this->permissionClass);
    }
}
