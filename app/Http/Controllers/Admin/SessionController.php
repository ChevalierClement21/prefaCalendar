<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Session;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class SessionController extends Controller
{
    /**
     * Display a listing of the sessions.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $sessions = Session::orderBy('year', 'desc')->orderBy('name')->get();
        return response()->json($sessions);
    }

    /**
     * Store a newly created session in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'year' => 'required|integer',
            'is_active' => 'boolean',
        ]);

        $session = Session::create($validated);

        // Si la session doit être active
        if ($request->input('is_active', false)) {
            $session->setAsActive();
        }

        return response()->json($session, Response::HTTP_CREATED);
    }

    /**
     * Display the specified session.
     *
     * @param  \App\Models\Session  $session
     * @return \Illuminate\Http\Response
     */
    public function show(Session $session)
    {
        return response()->json($session);
    }

    /**
     * Update the specified session in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Session  $session
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Session $session)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'year' => 'required|integer',
            'is_active' => 'boolean',
        ]);

        $session->update($validated);

        // Si la session doit être active
        if ($request->input('is_active', false) && !$session->is_active) {
            $session->setAsActive();
        }

        return response()->json($session);
    }

    /**
     * Remove the specified session from storage.
     *
     * @param  \App\Models\Session  $session
     * @return \Illuminate\Http\Response
     */
    public function destroy(Session $session)
    {
        // Vérifier si la session a des tours associés
        if ($session->tours()->count() > 0) {
            return response()->json([
                'message' => 'Impossible de supprimer cette session car elle contient des tournées.'
            ], Response::HTTP_CONFLICT);
        }

        $session->delete();
        return response()->json(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * Get the active session.
     *
     * @return \Illuminate\Http\Response
     */
    public function getActive()
    {
        $activeSession = Session::getActive();
        
        if (!$activeSession) {
            return response()->json(null, Response::HTTP_NOT_FOUND);
        }
        
        return response()->json($activeSession);
    }

    /**
     * Set the specified session as active.
     *
     * @param  \App\Models\Session  $session
     * @return \Illuminate\Http\Response
     */
    public function setActive(Session $session)
    {
        $success = $session->setAsActive();
        
        if ($success) {
            return response()->json($session);
        }
        
        return response()->json([
            'message' => 'Impossible de définir cette session comme active.'
        ], Response::HTTP_INTERNAL_SERVER_ERROR);
    }
}
