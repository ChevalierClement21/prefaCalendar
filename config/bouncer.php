<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Bouncer Database Tables
    |--------------------------------------------------------------------------
    |
    | Customize the names of the tables for abilities, roles, and their assignment.
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
    | Bouncer Cache
    |--------------------------------------------------------------------------
    |
    | Configure the ability check caching settings for Bouncer.
    |
    */

    'cache' => [
        'enabled' => true,
        'store' => null,
        'prefix' => 'bouncer',
    ],

];
