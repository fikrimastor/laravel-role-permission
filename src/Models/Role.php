<?php

namespace Fikrimastor\LaravelRolePermission\Models;

use Fikrimastor\LaravelRolePermission\Traits\HasRolePermissionTraits;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Role extends Model
{
    use HasRolePermissionTraits;
    protected $table = 'roles';

    protected $guarded = [];

    protected $with = ['permissions'];

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'role_user', 'role_id', 'user_id');
    }
}
