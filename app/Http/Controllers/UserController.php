<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Silber\Bouncer\BouncerFacade as Bouncer;

class UserController extends Controller
{

    /**
     * Display a listing of the users.
     */
    public function index()
    {
        Gate::authorize('viewUsers');

        $pendingUsers = User::where('approved', false)->get();
        $approvedUsers = User::where('approved', true)->get();

        return view('users.index', compact('pendingUsers', 'approvedUsers'));
    }

    /**
     * Approve a user.
     */
    public function approve(User $user)
    {
        Gate::authorize('approveUsers');

        $user->approved = true;
        $user->save();

        return redirect()->route('users.index')
            ->with('success', 'L\'utilisateur a été approuvé avec succès.');
    }

    /**
     * Reject a user (delete).
     */
    public function reject(User $user)
    {
        Gate::authorize('approveUsers');

        $user->delete();

        return redirect()->route('users.index')
            ->with('success', 'L\'utilisateur a été rejeté et supprimé avec succès.');
    }

    /**
     * Assign admin role to a user.
     */
    public function assignAdmin(User $user)
    {
        Gate::authorize('manageRoles');

        Bouncer::assign('admin')->to($user);

        return redirect()->route('users.index')
            ->with('success', 'L\'utilisateur a été promu administrateur avec succès.');
    }

    /**
     * Remove admin role from a user.
     */
    public function removeAdmin(User $user)
    {
        Gate::authorize('manageRoles');

        Bouncer::retract('admin')->from($user);

        return redirect()->route('users.index')
            ->with('success', 'Le rôle d\'administrateur a été retiré avec succès.');
    }
}
