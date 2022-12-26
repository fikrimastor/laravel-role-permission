<?php

namespace Fikrimastor\LaravelRolePermission\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Permission extends Model
{
    protected $table = 'permissions';

    protected $guarded = [];

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(
            config('permission.models.role', Role::class),
            config('permission.table_names.role_permission', 'permission_role'),
            'role_id',
            'permission_id'
        );
    }
}
