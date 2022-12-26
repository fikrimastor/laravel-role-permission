<?php

return [
    'models' => [
        'permission' => \Fikrimastor\LaravelRolePermission\Models\Permission::class,
        'role' => \Fikrimastor\LaravelRolePermission\Models\Role::class,
    ],

    'table_names' => [
        'roles' => 'roles',
        'permissions' => 'permissions',
    ],

    'cache' => [
        'expiration_time' => '',
        'key' => 'fikrimastor.permissions.cache',
        'store' => 'default',
    ],
];
