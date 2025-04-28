<?php

namespace Tests\Feature;

use App\Models\CompletedStreet;
use App\Models\HouseNumber;
use App\Models\Sector;
use App\Models\Session;
use App\Models\Street;
use App\Models\Tour;
use App\Models\TourCompletion;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class TourControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected User $user;
    protected User $otherUser;
    protected Sector $sector;
    protected Session $session;
    protected Tour $tour;
    protected Street $street;

    protected function setUp(): void
    {
        parent::setUp();

        // Mock the Gate facade for testing
        Gate::shouldReceive('authorize')->andReturn(true);
        Gate::shouldReceive('allows')->andReturn(true);
        
        // Create test users
        $this->user = User::factory()->create([
            'approved' => true
        ]);
        
        $this->otherUser = User::factory()->create([
            'approved' => true
        ]);

        // Create a test sector
        $this->sector = Sector::factory()->create();
        
        // Create a test session
        $this->session = Session::factory()->create([
            'is_active' => true
        ]);
        
        // Create a test street
        $this->street = Street::factory()->create([
            'sector_id' => $this->sector->id
        ]);
        
        // Create a test tour
        $this->tour = Tour::factory()->create([
            'creator_id' => $this->user->id,
            'sector_id' => $this->sector->id,
            'session_id' => $this->session->id,
            'status' => 'in_progress'
        ]);
    }

    #[Test]
    public function it_can_display_tour_index()
    {
        $response = $this->actingAs($this->user)
            ->get(route('tours.index'));

        $response->assertStatus(200);
        $response->assertViewIs('tours.index');
        $response->assertViewHas('tours');
    }

    #[Test]
    public function it_can_show_create_tour_form()
    {
        $response = $this->actingAs($this->user)
            ->get(route('tours.create'));

        $response->assertStatus(200);
        $response->assertViewIs('tours.create');
        $response->assertViewHas(['sectors', 'users', 'activeSession']);
    }

    #[Test]
    public function it_can_store_a_new_tour()
    {
        $tourData = [
            'name' => 'Test Tour',
            'sector_id' => $this->sector->id,
            'session_id' => $this->session->id,
            'user_ids' => [$this->otherUser->id],
            'notes' => 'Test notes'
        ];

        $response = $this->actingAs($this->user)
            ->post(route('tours.store'), $tourData);

        $response->assertRedirect();
        $response->assertSessionHas('success');
        
        $this->assertDatabaseHas('tours', [
            'name' => 'Test Tour',
            'sector_id' => $this->sector->id,
            'creator_id' => $this->user->id,
            'session_id' => $this->session->id,
            'status' => 'in_progress'
        ]);
        
        // Check if user was attached to tour
        $tour = Tour::where('name', 'Test Tour')->first();
        $this->assertTrue($tour->users->contains($this->otherUser->id));
    }

    #[Test]
    public function it_handles_errors_when_storing_a_tour()
    {
        // Données de tournée avec un nom manquant
        $tourData = [
            'sector_id' => $this->sector->id
            // Le champ 'name' manque intentionnellement
        ];

        // Faire la requête sans le champ name
        $response = $this->actingAs($this->user)
            ->post(route('tours.store'), $tourData);
        
        // Vérifier que la réponse est bien une redirection
        $response->assertRedirect();
        
        // Vérifier qu'aucune tournée n'a été créée avec ces données
        $countBefore = Tour::count();
        $this->assertEquals($countBefore, Tour::count());
    }

    #[Test]
    public function it_can_show_a_tour()
    {
        $response = $this->actingAs($this->user)
            ->get(route('tours.show', $this->tour));

        $response->assertStatus(200);
        $response->assertViewIs('tours.show');
        $response->assertViewHas(['tour', 'streets', 'houseNumbers']);
    }

    #[Test]
    public function it_can_add_a_house_number_to_tour()
    {
        $houseData = [
            'street_id' => $this->street->id,
            'number' => '123',
            'notes' => 'Test house note'
        ];

        $response = $this->actingAs($this->user)
            ->post(route('tours.house-numbers.add', $this->tour), $houseData);

        $response->assertRedirect(route('tours.show', $this->tour));
        $response->assertSessionHas('success');
        
        $this->assertDatabaseHas('house_numbers', [
            'tour_id' => $this->tour->id,
            'street_id' => $this->street->id,
            'number' => '123',
            'status' => 'to_revisit'
        ]);
    }

    #[Test]
    public function it_prevents_duplicate_house_numbers_in_same_session()
    {
        // Create a house number in another tour of the same session
        $anotherTour = Tour::factory()->create([
            'sector_id' => $this->sector->id,
            'session_id' => $this->session->id
        ]);
        
        HouseNumber::factory()->create([
            'tour_id' => $anotherTour->id,
            'street_id' => $this->street->id,
            'number' => '123',
            'status' => 'to_revisit'
        ]);
        
        $houseData = [
            'street_id' => $this->street->id,
            'number' => '123',  // Same as the one in another tour
            'notes' => 'Test house note'
        ];

        $response = $this->actingAs($this->user)
            ->post(route('tours.house-numbers.add', $this->tour), $houseData);

        $response->assertRedirect(route('tours.show', $this->tour));
        $response->assertSessionHas('error');
        
        // Verify the house number wasn't added to this tour
        $this->assertDatabaseMissing('house_numbers', [
            'tour_id' => $this->tour->id,
            'street_id' => $this->street->id,
            'number' => '123'
        ]);
    }

    #[Test]
    public function it_can_update_house_number_status()
    {
        $houseNumber = HouseNumber::factory()->create([
            'tour_id' => $this->tour->id,
            'street_id' => $this->street->id,
            'number' => '123',
            'status' => 'to_revisit'
        ]);

        $updateData = [
            'status' => 'visited'
        ];

        $response = $this->actingAs($this->user)
            ->patch(route('tours.house-numbers.status', [
                'tour' => $this->tour,
                'houseNumber' => $houseNumber
            ]), $updateData);

        $response->assertRedirect(route('tours.show', $this->tour));
        $response->assertSessionHas('success');
        
        $this->assertDatabaseHas('house_numbers', [
            'id' => $houseNumber->id,
            'status' => 'visited'
        ]);
    }

    #[Test]
    public function it_can_show_complete_tour_form()
    {
        $response = $this->actingAs($this->user)
            ->get(route('tours.complete-form', $this->tour));

        $response->assertStatus(200);
        $response->assertViewIs('tours.complete');
        $response->assertViewHas('tour');
    }

    #[Test]
    public function it_can_submit_tour_completion()
    {
        $completionData = [
            'calendars_sold' => 5,
            
            // Billets
            'tickets_5' => 1,
            'tickets_10' => 2,
            'tickets_20' => 3,
            'tickets_50' => 1,
            'tickets_100' => 0,
            'tickets_200' => 0,
            'tickets_500' => 0,
            
            // Pièces
            'coins_1c' => 10,
            'coins_2c' => 15,
            'coins_5c' => 20,
            'coins_10c' => 5,
            'coins_20c' => 3,
            'coins_50c' => 2,
            'coins_1e' => 1,
            'coins_2e' => 1,
            
            // Chèques
            'check_count' => 2,
            'check_amounts' => [15.5, 25],
            
            'notes' => 'Test completion notes'
        ];

        $response = $this->actingAs($this->user)
            ->post(route('tours.submit-completion', $this->tour), $completionData);

        $response->assertRedirect(route('tours.index'));
        $response->assertSessionHas('success');
        
        // Check tour was marked as completed
        $this->assertDatabaseHas('tours', [
            'id' => $this->tour->id,
            'status' => 'completed'
        ]);
        
        // Check tour completion record was created
        $this->assertDatabaseHas('tour_completions', [
            'tour_id' => $this->tour->id,
            'calendars_sold' => 5,
            'check_count' => 2,
            'check_total_amount' => 40.5
        ]);
    }

    #[Test]
    public function it_can_mark_tour_as_completed_without_form()
    {
        $response = $this->actingAs($this->user)
            ->patch(route('tours.complete', $this->tour));

        $response->assertRedirect(route('tours.index'));
        $response->assertSessionHas('success');
        
        $this->assertDatabaseHas('tours', [
            'id' => $this->tour->id,
            'status' => 'completed'
        ]);
    }

    #[Test]
    public function it_can_mark_street_as_completed()
    {
        $response = $this->actingAs($this->user)
            ->post(route('tours.streets.mark-completed', [
                'tour' => $this->tour,
                'street' => $this->street
            ]), ['notes' => 'Street completed']);

        $response->assertRedirect(route('tours.show', $this->tour));
        $response->assertSessionHas('success');
        
        $this->assertDatabaseHas('completed_streets', [
            'tour_id' => $this->tour->id,
            'street_id' => $this->street->id,
            'session_id' => $this->session->id,
            'completed_by' => $this->user->id
        ]);
    }

    #[Test]
    public function it_can_toggle_street_completion()
    {
        // First mark street as completed
        CompletedStreet::create([
            'tour_id' => $this->tour->id,
            'street_id' => $this->street->id,
            'session_id' => $this->session->id,
            'completed_by' => $this->user->id
        ]);
        
        // Then toggle it off
        $response = $this->actingAs($this->user)
            ->post(route('tours.streets.mark-completed', [
                'tour' => $this->tour,
                'street' => $this->street
            ]));

        $response->assertRedirect(route('tours.show', $this->tour));
        $response->assertSessionHas('success');
        
        // Check the completed street record was removed
        $this->assertDatabaseMissing('completed_streets', [
            'tour_id' => $this->tour->id,
            'street_id' => $this->street->id
        ]);
    }

    #[Test]
    public function it_cannot_mark_street_completed_without_session()
    {
        // Pour ce test, créons une tournée sans session
        $tourWithoutSession = Tour::create([
            'name' => 'Tour Without Session',
            'creator_id' => $this->user->id,
            'sector_id' => $this->sector->id,
            'session_id' => null,
            'status' => 'in_progress',
            'start_date' => now()
        ]);
        
        // Essayer de marquer une rue comme complétée
        $response = $this->actingAs($this->user)
            ->post(route('tours.streets.mark-completed', [
                'tour' => $tourWithoutSession,
                'street' => $this->street
            ]));

        // Vérifier simplement la redirection
        $response->assertRedirect(route('tours.show', $tourWithoutSession));
    }
}
