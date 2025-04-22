<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Sector;
use App\Models\Street;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StreetController extends Controller
{
    /**
     * Display a listing of the streets.
     */
    public function index(): View
    {
        $streets = Street::with('sector')->orderBy('name')->get();
        
        return view('admin.streets.index', compact('streets'));
    }

    /**
     * Show the form for creating a new street.
     */
    public function create(): View
    {
        $sectors = Sector::orderBy('name')->get();
        
        return view('admin.streets.create', compact('sectors'));
    }

    /**
     * Store a newly created street in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'sector_id' => 'required|exists:sectors,id',
            'name' => 'required|string|max:255',
            'postal_code' => 'nullable|string|max:10',
            'city' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        Street::create($validated);

        return redirect()->route('admin.streets.index')
            ->with('success', 'Rue créée avec succès.');
    }

    /**
     * Display the specified street.
     */
    public function show(Street $street): View
    {
        $street->load('sector');
        
        return view('admin.streets.show', compact('street'));
    }

    /**
     * Show the form for editing the specified street.
     */
    public function edit(Street $street): View
    {
        $sectors = Sector::orderBy('name')->get();
        
        return view('admin.streets.edit', compact('street', 'sectors'));
    }

    /**
     * Update the specified street in storage.
     */
    public function update(Request $request, Street $street): RedirectResponse
    {
        $validated = $request->validate([
            'sector_id' => 'required|exists:sectors,id',
            'name' => 'required|string|max:255',
            'postal_code' => 'nullable|string|max:10',
            'city' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        $street->update($validated);

        return redirect()->route('admin.streets.index')
            ->with('success', 'Rue mise à jour avec succès.');
    }

    /**
     * Remove the specified street from storage.
     */
    public function destroy(Street $street): RedirectResponse
    {
        $street->delete();

        return redirect()->route('admin.streets.index')
            ->with('success', 'Rue supprimée avec succès.');
    }
    
    /**
     * Create a new street for a specific sector.
     */
    public function createForSector(Sector $sector): View
    {
        return view('admin.streets.create', [
            'sectors' => Sector::orderBy('name')->get(),
            'selected_sector' => $sector
        ]);
    }
}
