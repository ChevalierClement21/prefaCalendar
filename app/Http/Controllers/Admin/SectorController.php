<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Sector;
use App\Models\Street;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SectorController extends Controller
{
    /**
     * Display a listing of the sectors.
     */
    public function index(): View
    {
        $sectors = Sector::withCount('streets')->orderBy('name')->get();
        
        return view('admin.sectors.index', compact('sectors'));
    }

    /**
     * Show the form for creating a new sector.
     */
    public function create(): View
    {
        return view('admin.sectors.create');
    }

    /**
     * Store a newly created sector in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'color' => 'nullable|string|max:7',
        ]);

        Sector::create($validated);

        return redirect()->route('admin.sectors.index')
            ->with('success', 'Secteur créé avec succès.');
    }

    /**
     * Display the specified sector.
     */
    public function show(Sector $sector): View
    {
        $sector->load('streets');
        
        return view('admin.sectors.show', compact('sector'));
    }

    /**
     * Show the form for editing the specified sector.
     */
    public function edit(Sector $sector): View
    {
        return view('admin.sectors.edit', compact('sector'));
    }

    /**
     * Update the specified sector in storage.
     */
    public function update(Request $request, Sector $sector): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'color' => 'nullable|string|max:7',
        ]);

        $sector->update($validated);

        return redirect()->route('admin.sectors.index')
            ->with('success', 'Secteur mis à jour avec succès.');
    }

    /**
     * Remove the specified sector from storage.
     */
    public function destroy(Sector $sector): RedirectResponse
    {
        $sector->delete();

        return redirect()->route('admin.sectors.index')
            ->with('success', 'Secteur supprimé avec succès.');
    }
}
