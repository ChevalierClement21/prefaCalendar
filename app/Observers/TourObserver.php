<?php

namespace App\Observers;

use App\Models\Tour;
use App\Models\Session;

class TourObserver
{
    /**
     * Handle the Tour "creating" event.
     */
    public function creating(Tour $tour): void
    {
        // If session_id is not already set, assign the active session
        if (!$tour->session_id) {
            $activeSession = Session::getActive();
            
            if ($activeSession) {
                $tour->session_id = $activeSession->id;
                \Illuminate\Support\Facades\Log::info('TourObserver: Session active trouvée et assignée - ID: ' . $activeSession->id);
            } else {
                \Illuminate\Support\Facades\Log::warning('TourObserver: Aucune session active trouvée');
            }
        } else {
            \Illuminate\Support\Facades\Log::info('TourObserver: session_id déjà défini - ID: ' . $tour->session_id);
        }
    }
}
