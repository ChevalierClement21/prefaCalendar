<?php

namespace App\Http\Controllers;

use App\Models\HouseNumber;
use App\Models\Sector;
use App\Models\Session;
use App\Models\Street;
use App\Models\Tour;
use App\Models\TourCompletion;
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
        $users = User::where('approved', true)
            ->where('id', '!=', Auth::id())
            ->get();
        
        // Récupérer la session active
        $activeSession = Session::where('is_active', true)->first();
        
        return view('tours.create', compact('sectors', 'users', 'activeSession'));
    }

    /**
     * Store a newly created tour in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        \Illuminate\Support\Facades\Log::info('Méthode store appelée');
        
        try {
            Gate::authorize('create-tour');
            
            \Illuminate\Support\Facades\Log::info('Données du formulaire:', $request->all());
            
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'sector_id' => 'required|exists:sectors,id',
                'session_id' => 'nullable|exists:calendar_sessions,id',
                'user_ids' => 'nullable|array',
                'user_ids.*' => 'exists:users,id',
                'notes' => 'nullable|string',
            ]);
            
            \Illuminate\Support\Facades\Log::info('Validation réussie');

            $tourData = [
                'name' => $validated['name'],
                'sector_id' => $validated['sector_id'],
                'creator_id' => Auth::id(),
                'status' => 'in_progress',
                'start_date' => now(),
                'notes' => $validated['notes'] ?? null,
            ];
            
            // Ajouter session_id explicitement s'il est fourni dans le formulaire
            if (isset($validated['session_id'])) {
                $tourData['session_id'] = $validated['session_id'];
                \Illuminate\Support\Facades\Log::info('Session ID inclus explicitement: ' . $validated['session_id']);
            }
            
            $tour = Tour::create($tourData);
            \Illuminate\Support\Facades\Log::info('Tournée créée avec l\'ID: ' . $tour->id);

            if (isset($validated['user_ids'])) {
                $tour->users()->attach($validated['user_ids']);
                \Illuminate\Support\Facades\Log::info('Utilisateurs associés');
            }
        
            // Récupérer les numéros de maisons à revisiter du même secteur
            // Regrouper par street_id et number pour éviter les doublons
            $housesToRevisit = HouseNumber::where('status', 'to_revisit')
                ->whereHas('tour', function ($query) use ($validated) {
                    $query->where('sector_id', $validated['sector_id']);
                })
                ->whereHas('street', function ($query) use ($validated) {
                    $query->where('sector_id', $validated['sector_id']);
                })
                ->select('street_id', 'number', 'notes')
                ->distinct()
                ->get();
                
            \Illuminate\Support\Facades\Log::info('Nombre de maisons à revisiter: ' . $housesToRevisit->count());
                
            // Vérifier si chaque maison à revisiter existe déjà dans la nouvelle tournée avant de la créer
            foreach ($housesToRevisit as $house) {
                // Vérifier si cette combinaison n'existe pas déjà dans la nouvelle tournée
                $exists = HouseNumber::where('tour_id', $tour->id)
                    ->where('street_id', $house->street_id)
                    ->where('number', $house->number)
                    ->exists();
                    
                if (!$exists) {
                    HouseNumber::create([
                        'tour_id' => $tour->id,
                        'street_id' => $house->street_id,
                        'number' => $house->number,
                        'status' => 'to_revisit',
                        'notes' => $house->notes
                    ]);
                }
            }

            \Illuminate\Support\Facades\Log::info('Redirection vers la page de la tournée');
            return redirect()->route('tours.show', $tour)
                ->with('success', 'Tournée créée avec succès.');
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Erreur lors de la création de la tournée: ' . $e->getMessage());
            \Illuminate\Support\Facades\Log::error($e->getTraceAsString());
            
            return redirect()->back()
                ->withInput()
                ->withErrors(['error' => 'Une erreur s\'est produite lors de la création de la tournée: ' . $e->getMessage()]);
        }
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
     * Show the form for completing a tour.
     */
    public function showCompleteForm(Tour $tour): View
    {
        Gate::authorize('complete-tour', $tour);
        
        return view('tours.complete', compact('tour'));
    }

    /**
     * Submit completion form for a tour.
     */
    public function submitCompletion(Request $request, Tour $tour): RedirectResponse
    {
        Gate::authorize('complete-tour', $tour);
        
        $validated = $request->validate([
            'calendars_sold' => 'required|integer|min:0',
            
            // Billets
            'tickets_5' => 'required|integer|min:0',
            'tickets_10' => 'required|integer|min:0',
            'tickets_20' => 'required|integer|min:0',
            'tickets_50' => 'required|integer|min:0',
            'tickets_100' => 'required|integer|min:0',
            'tickets_200' => 'required|integer|min:0',
            'tickets_500' => 'required|integer|min:0',
            
            // Pièces
            'coins_1c' => 'required|integer|min:0',
            'coins_2c' => 'required|integer|min:0',
            'coins_5c' => 'required|integer|min:0',
            'coins_10c' => 'required|integer|min:0',
            'coins_20c' => 'required|integer|min:0',
            'coins_50c' => 'required|integer|min:0',
            'coins_1e' => 'required|integer|min:0',
            'coins_2e' => 'required|integer|min:0',
            
            // Chèques
            'check_count' => 'required|integer|min:0',
            'check_amounts' => 'required_if:check_count,>0|array',
            'check_amounts.*' => 'required|numeric|min:0',
            
            'notes' => 'nullable|string',
        ]);
        
        // Calculer le montant total des chèques
        $checkTotalAmount = 0;
        $checkAmounts = [];
        
        if (isset($validated['check_amounts']) && is_array($validated['check_amounts'])) {
            foreach ($validated['check_amounts'] as $amount) {
                if (is_numeric($amount)) {
                    $checkTotalAmount += (float) $amount;
                    $checkAmounts[] = (float) $amount;
                }
            }
        }
        
        // Créer l'enregistrement de fin de tournée
        $completion = new TourCompletion([
            'tour_id' => $tour->id,
            'calendars_sold' => $validated['calendars_sold'],
            
            // Billets
            'tickets_5' => $validated['tickets_5'],
            'tickets_10' => $validated['tickets_10'],
            'tickets_20' => $validated['tickets_20'],
            'tickets_50' => $validated['tickets_50'],
            'tickets_100' => $validated['tickets_100'],
            'tickets_200' => $validated['tickets_200'],
            'tickets_500' => $validated['tickets_500'],
            
            // Pièces
            'coins_1c' => $validated['coins_1c'],
            'coins_2c' => $validated['coins_2c'],
            'coins_5c' => $validated['coins_5c'],
            'coins_10c' => $validated['coins_10c'],
            'coins_20c' => $validated['coins_20c'],
            'coins_50c' => $validated['coins_50c'],
            'coins_1e' => $validated['coins_1e'],
            'coins_2e' => $validated['coins_2e'],
            
            // Chèques
            'check_count' => $validated['check_count'],
            'check_total_amount' => $checkTotalAmount,
            'check_amounts' => $checkAmounts,
            
            'notes' => $validated['notes'] ?? null,
        ]);
        
        // Calculer le montant total
        $completion->calculateTotal();
        
        // Sauvegarder
        $completion->save();
        
        // Marquer la tournée comme terminée
        $tour->update([
            'status' => 'completed',
            'end_date' => now(),
        ]);

        return redirect()->route('tours.index')
            ->with('success', 'Tournée terminée avec succès. Bilan enregistré.');
    }

    /**
     * Complete the tour without completion form (fallback).  
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