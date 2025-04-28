<?php

namespace Tests\Unit;

use App\Http\Controllers\TourController;
use App\Models\CompletedStreet;
use App\Models\HouseNumber;
use App\Models\Sector;
use App\Models\Session;
use App\Models\Street;
use App\Models\Tour;
use App\Models\TourCompletion;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Tests\TestCase;
use Mockery;
use PHPUnit\Framework\Attributes\Test;

class TourControllerTest extends TestCase
{
    use DatabaseTransactions; // Utiliser DatabaseTransactions au lieu de RefreshDatabase pour de meilleures performances

    /**
     * Tester la méthode index() du contrôleur qui retourne une vue avec les tournées
     */
    #[Test]
    public function controller_index_method_returns_view_with_tours()
    {
        // Créer un utilisateur et un secteur
        $user = User::factory()->create(['approved' => true]);
        $sector = Sector::factory()->create();
        
        // Créer manuellement des tournées avec tous les champs requis
        Tour::create([
            'name' => 'Test Tour 1',
            'sector_id' => $sector->id,
            'creator_id' => $user->id,
            'status' => 'in_progress',
            'start_date' => now(),
        ]);
        
        Tour::create([
            'name' => 'Test Tour 2',
            'sector_id' => $sector->id,
            'creator_id' => $user->id,
            'status' => 'in_progress',
            'start_date' => now(),
        ]);
        
        // Mocker Gate pour autoriser l'action
        Gate::shouldReceive('authorize')->with('view-any-tours')->andReturn(true);
        
        // Mocker Auth pour simuler l'utilisateur connecté
        Auth::shouldReceive('id')->andReturn($user->id);
        
        // Exécuter la méthode du contrôleur
        $controller = new TourController();
        $view = $controller->index();
        
        // Vérifier que la vue est correcte
        $this->assertInstanceOf(View::class, $view);
        $this->assertEquals('tours.index', $view->getName());
        $this->assertArrayHasKey('tours', $view->getData());
        $this->assertInstanceOf(LengthAwarePaginator::class, $view->getData()['tours']);
    }
    
    /**
     * Tester la méthode create() du contrôleur qui retourne une vue avec les données nécessaires
     */
    #[Test]
    public function controller_create_method_returns_view_with_required_data()
    {
        // Créer des données de test
        $user = User::factory()->create(['approved' => true]);
        Sector::factory()->count(2)->create();
        User::factory()->count(2)->create(['approved' => true]);
        Session::factory()->create(['is_active' => true]);
        
        // Mocker Gate pour autoriser l'action
        Gate::shouldReceive('authorize')->with('create-tour')->andReturn(true);
        
        // Mocker Auth pour simuler l'utilisateur connecté
        Auth::shouldReceive('id')->andReturn($user->id);
        
        // Exécuter la méthode du contrôleur
        $controller = new TourController();
        $view = $controller->create();
        
        // Vérifier que la vue est correcte
        $this->assertInstanceOf(View::class, $view);
        $this->assertEquals('tours.create', $view->getName());
        $this->assertArrayHasKey('sectors', $view->getData());
        $this->assertArrayHasKey('users', $view->getData());
        $this->assertArrayHasKey('activeSession', $view->getData());
    }
    
    /**
     * Tester la méthode show() du contrôleur qui retourne une vue avec les informations d'une tournée
     */
    #[Test]
    public function controller_show_method_returns_view_with_tour_data()
    {
        // Créer un utilisateur et un secteur
        $user = User::factory()->create(['approved' => true]);
        $sector = Sector::factory()->create();
        
        // Créer manuellement une tournée
        $tour = Tour::create([
            'name' => 'Test Tour',
            'sector_id' => $sector->id,
            'creator_id' => $user->id,
            'status' => 'in_progress',
            'start_date' => now(),
        ]);
        
        // Créer une rue pour le secteur
        $street = Street::factory()->create(['sector_id' => $sector->id]);
        
        // Mocker Gate pour autoriser l'action
        Gate::shouldReceive('authorize')->with('view-tour', \Mockery::type(Tour::class))->andReturn(true);
        
        // Exécuter la méthode du contrôleur
        $controller = new TourController();
        $view = $controller->show($tour);
        
        // Vérifier que la vue est correcte
        $this->assertInstanceOf(View::class, $view);
        $this->assertEquals('tours.show', $view->getName());
        $this->assertArrayHasKey('tour', $view->getData());
        $this->assertArrayHasKey('streets', $view->getData());
        $this->assertArrayHasKey('houseNumbers', $view->getData());
        $this->assertEquals($tour->id, $view->getData()['tour']->id);
    }
}
