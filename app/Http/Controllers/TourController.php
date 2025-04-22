<?php

namespace App\Http\Controllers;

use App\Models\HouseNumber;
use App\Models\Sector;
use App\Models\Street;
use App\Models\Tour;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class TourController extends Controller
{
    /**
     * No constructor needed for Laravel 12 style authorization
     * Authorization will be handled directly in the methods
     */

    /**
     * Display a listing of the tours.
     */
    public function index(): View
    {
        Gate::authorize('view-any-tours');
        $tours = Tour::with(['sector', 'creator'])
            ->where('creator_id', Auth::id())
            ->orWhereHas('users', function ($query) {
                $query->where('users.id', Auth::id());
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('tours.index', compact('tours'));
    }

    /**
     * Show the form for creating a new tour.
     */
    public function create(): View
    {
        Gate::authorize('create-tour');
        
        $sectors = Sector::all();
        $users = User::where('approved', true)->get();
        
        return view('tours.create', compact('sectors', 'users'));
    }

    /**
     * Store a newly created tour in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        Gate::authorize('create-tour');
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'sector_id' => 'required|exists:sectors,id',
            'user_ids' => 'nullable|array',
            'user_ids.*' => 'exists:users,id',
            'notes' => 'nullable|string',
        ]);

        $tour = Tour::create([
            'name' => $validated['name'],
            'sector_id' => $validated['sector_id'],
            'creator_id' => Auth::id(),
            'status' => 'in_progress',
            'start_date' => now(),
            'notes' => $validated['notes'] ?? null,
        ]);

        if (isset($validated['user_ids'])) {
            $tour->users()->attach($validated['user_ids']);
        }

        return redirect()->route('tours.show', $tour)
            ->with('success', 'Tournée créée avec succès.');
    }

    /**
     * Display the specified tour.
     */
    public function show(Tour $tour): View
    {
        Gate::authorize('view-tour', $tour);
        
        $tour->load(['sector', 'users', 'creator']);
        $streets = Street::where('sector_id', $tour->sector_id)->get();
        $houseNumbers = HouseNumber::where('tour_id', $tour->id)
            ->with('street')
            ->get()
            ->groupBy('street_id');

        return view('tours.show', compact('tour', 'streets', 'houseNumbers'));
    }

    /**
     * Add a house number to the tour.
     */
    public function addHouseNumber(Request $request, Tour $tour): RedirectResponse
    {
        Gate::authorize('add-house-number', $tour);
        $validated = $request->validate([
            'street_id' => 'required|exists:streets,id',
            'number' => 'required|string|max:10',
            'notes' => 'nullable|string',
        ]);

        HouseNumber::create([
            'tour_id' => $tour->id,
            'street_id' => $validated['street_id'],
            'number' => $validated['number'],
            'status' => 'to_revisit',
            'notes' => $validated['notes'] ?? null,
        ]);

        return redirect()->route('tours.show', $tour)
            ->with('success', 'Numéro de maison ajouté avec succès.');
    }

    /**
     * Update the status of a house number.
     */
    public function updateHouseNumberStatus(Request $request, Tour $tour, HouseNumber $houseNumber): RedirectResponse
    {
        Gate::authorize('update-house-number-status', [$tour, $houseNumber]);
        $validated = $request->validate([
            'status' => 'required|in:to_revisit,visited,skipped',
        ]);

        $houseNumber->update([
            'status' => $validated['status'],
        ]);

        return redirect()->route('tours.show', $tour)
            ->with('success', 'Statut du numéro de maison mis à jour avec succès.');
    }

    /**
     * Complete the tour.
     */
    public function complete(Tour $tour): RedirectResponse
    {
        Gate::authorize('complete-tour', $tour);
        $tour->update([
            'status' => 'completed',
            'end_date' => now(),
        ]);

        return redirect()->route('tours.index')
            ->with('success', 'Tournée terminée avec succès.');
    }
}