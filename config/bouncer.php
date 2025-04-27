<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Cache Driver
    |--------------------------------------------------------------------------
    |
    | This value determines which cache driver Bouncer will use to cache
    | permissions and role checks. The "null" driver will use nothing.
    |
    */

    'cache' => null,

    /*
    |--------------------------------------------------------------------------
    | Tables
    |--------------------------------------------------------------------------
    |
    | These are the tables that Bouncer will use to store roles and abilities.
    |
    */

    'tables' => [
        'abilities' => 'abilities',
        'assigned_roles' => 'assigned_roles',
        'permissions' => 'permissions',
        'roles' => 'roles',
    ],

    /*
    |--------------------------------------------------------------------------
    | Models
    |--------------------------------------------------------------------------
    |
    | These are the models that Bouncer will use to store roles and abilities.
    |
    */

    'models' => [
        'ability' => \Silber\Bouncer\Database\Ability::class,
        'role' => \Silber\Bouncer\Database\Role::class,
    ],
];
