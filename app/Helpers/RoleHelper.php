<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Auth;

class RoleHelper
{
    /**
     * Vérifie si l'utilisateur connecté a un rôle spécifique.
     *
     * @param string $role
     * @return bool
     */
    public static function isAdmin()
    {
        if (!Auth::check()) {
            return false;
        }
        
        return Auth::user()->hasRole('admin');
    }

    /**
     * Vérifie si l'utilisateur connecté a un rôle spécifique.
     *
     * @param string $role
     * @return bool
     */
    public static function hasRole($role)
    {
        if (!Auth::check()) {
            return false;
        }
        
        return Auth::user()->hasRole($role);
    }
}
