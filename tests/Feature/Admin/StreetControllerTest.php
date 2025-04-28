<?php

namespace Tests\Feature\Admin;

use App\Models\Sector;
use App\Models\Street;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Silber\Bouncer\BouncerFacade as Bouncer;
use Tests\TestCase;

class StreetControllerTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    /**
     * Setup the test environment.
     */
    protected function setUp(): void
    {
        parent::setUp();

        // Create admin and user roles for testing
        Bouncer::role()->firstOrCreate(['name' => 'admin']);
        Bouncer::role()->firstOrCreate(['name' => 'user']);

        // Define abilities
        Bouncer::ability()->firstOrCreate(['name' => 'access-admin-panel']);
        Bouncer::ability()->firstOrCreate(['name' => 'manage-sectors']);
        Bouncer::ability()->firstOrCreate(['name' => 'manage-streets']);

        // Set role permissions
        if (!Bouncer::role()->where('name', 'admin')->first()->abilities()->where('name', 'access-admin-panel')->exists()) {
            Bouncer::allow('admin')->to('access-admin-panel');
            Bouncer::allow('admin')->to('manage-sectors');
            Bouncer::allow('admin')->to('manage-streets');
        }

        if (!Bouncer::role()->where('name', 'user')->first()->abilities()->where('name', 'access-admin-panel')->exists()) {
            Bouncer::forbid('user')->to('access-admin-panel');
            Bouncer::forbid('user')->to('manage-sectors');
            Bouncer::forbid('user')->to('manage-streets');
        }
    }

    /**
     * Create and return an admin user.
     */
    private function createAdminUser(): User
    {
        $admin = User::factory()->create();
        Bouncer::assign('admin')->to($admin);
        Bouncer::refresh();

        return $admin;
    }

    /**
     * Create and return a regular user.
     */
    private function createRegularUser(): User
    {
        $user = User::factory()->create();
        Bouncer::assign('user')->to($user);
        Bouncer::refresh();

        return $user;
    }


    /**
     * Test the index method displays streets correctly.
     */
    public function test_index_displays_streets(): void
    {
        $admin = $this->createAdminUser();
        $sector = Sector::factory()->create();
        $streets = Street::factory()->count(3)->create(['sector_id' => $sector->id]);

        $response = $this->actingAs($admin)
                         ->get(route('admin.streets.index'));

        $response->assertStatus(200);
        $response->assertViewIs('admin.streets.index');
        $response->assertViewHas('streets');

        // Verify that all streets are passed to the view
        $viewStreets = $response->viewData('streets');
        $this->assertCount(3, $viewStreets);

        // Verify the streets are ordered by name
        $sortedStreets = $streets->sortBy('name')->values();
        $this->assertEquals($sortedStreets->pluck('id')->toArray(), $viewStreets->pluck('id')->toArray());
    }

    /**
     * Test the create method displays the create form.
     */
    public function test_create_displays_form(): void
    {
        $admin = $this->createAdminUser();

        $response = $this->actingAs($admin)
                         ->get(route('admin.streets.create'));

        $response->assertStatus(200);
        $response->assertViewIs('admin.streets.create');
        $response->assertViewHas('sectors');
    }

    /**
     * Test the store method creates a new street.
     */
    public function test_store_creates_new_street(): void
    {
        $admin = $this->createAdminUser();
        $sector = Sector::factory()->create();

        $streetData = [
            'sector_id' => $sector->id,
            'name' => $this->faker->streetName(),
            'postal_code' => $this->faker->postcode(),
            'city' => $this->faker->city(),
            'notes' => $this->faker->paragraph(),
        ];

        $response = $this->actingAs($admin)
                         ->post(route('admin.streets.store'), $streetData);

        // Check that we're redirected somewhere - the exact URL might vary
        $response->assertRedirect();
        // We can also check if the redirect URL contains 'streets'
        $this->assertStringContainsString('streets', $response->headers->get('Location'));
        $response->assertSessionHas('success', 'Rue créée avec succès.');

        // Verify the street was created in the database
        $this->assertDatabaseHas('streets', [
            'sector_id' => $streetData['sector_id'],
            'name' => $streetData['name'],
            'postal_code' => $streetData['postal_code'],
            'city' => $streetData['city'],
            'notes' => $streetData['notes'],
        ]);
    }

    /**
     * Test the store method validates the input.
     */
    public function test_store_validates_input(): void
    {
        $admin = $this->createAdminUser();

        // Try to create a street without a name (required field)
        $response = $this->actingAs($admin)
                         ->post(route('admin.streets.store'), [
                             'sector_id' => 999, // Non-existent sector ID
                             'postal_code' => $this->faker->postcode(),
                             'city' => $this->faker->city(),
                         ]);

        $response->assertSessionHasErrors(['name', 'sector_id']);
    }

    /**
     * Test the show method displays a street correctly.
     */
    public function test_show_displays_street(): void
    {
        $admin = $this->createAdminUser();
        $sector = Sector::factory()->create();
        $street = Street::factory()->create(['sector_id' => $sector->id]);

        $response = $this->actingAs($admin)
                         ->get(route('admin.streets.show', $street));

        $response->assertStatus(200);
        $response->assertViewIs('admin.streets.show');
        $response->assertViewHas('street');

        // Verify the street is loaded with its sector
        $viewStreet = $response->viewData('street');
        $this->assertEquals($street->id, $viewStreet->id);
        $this->assertEquals($sector->id, $viewStreet->sector->id);
    }

    /**
     * Test the edit method displays the edit form.
     */
    public function test_edit_displays_form(): void
    {
        $admin = $this->createAdminUser();
        $sector = Sector::factory()->create();
        $street = Street::factory()->create(['sector_id' => $sector->id]);

        $response = $this->actingAs($admin)
                         ->get(route('admin.streets.edit', $street));

        $response->assertStatus(200);
        $response->assertViewIs('admin.streets.edit');
        $response->assertViewHas('street');
        $response->assertViewHas('sectors');

        // Verify the correct street is passed to the view
        $viewStreet = $response->viewData('street');
        $this->assertEquals($street->id, $viewStreet->id);
    }

    /**
     * Test the update method updates a street.
     */
    public function test_update_updates_street(): void
    {
        $admin = $this->createAdminUser();
        $sector = Sector::factory()->create();
        $newSector = Sector::factory()->create();
        $street = Street::factory()->create(['sector_id' => $sector->id]);

        $updatedData = [
            'sector_id' => $newSector->id,
            'name' => 'Updated Street Name',
            'postal_code' => '12345',
            'city' => 'Updated City',
            'notes' => 'Updated notes',
        ];

        $response = $this->actingAs($admin)
                         ->put(route('admin.streets.update', $street), $updatedData);

        // Check that we're redirected somewhere related to streets
        $response->assertRedirect();
        $this->assertStringContainsString('streets', $response->headers->get('Location'));
        $response->assertSessionHas('success', 'Rue mise à jour avec succès.');

        // Verify the street was updated in the database
        $this->assertDatabaseHas('streets', [
            'id' => $street->id,
            'sector_id' => $updatedData['sector_id'],
            'name' => $updatedData['name'],
            'postal_code' => $updatedData['postal_code'],
            'city' => $updatedData['city'],
            'notes' => $updatedData['notes'],
        ]);
    }

    /**
     * Test the update method validates the input.
     */
    public function test_update_validates_input(): void
    {
        $admin = $this->createAdminUser();
        $sector = Sector::factory()->create();
        $street = Street::factory()->create(['sector_id' => $sector->id]);

        // Try to update a street without a name (required field)
        $response = $this->actingAs($admin)
                         ->put(route('admin.streets.update', $street), [
                             'sector_id' => 999, // Non-existent sector ID
                             'postal_code' => '12345',
                             'city' => 'Updated City',
                         ]);

        $response->assertSessionHasErrors(['name', 'sector_id']);
    }

    /**
     * Test the destroy method deletes a street.
     */
    public function test_destroy_deletes_street(): void
    {
        $admin = $this->createAdminUser();
        $sector = Sector::factory()->create();
        $street = Street::factory()->create(['sector_id' => $sector->id]);

        $response = $this->actingAs($admin)
                         ->delete(route('admin.streets.destroy', $street));

        $response->assertRedirect();
        $this->assertStringContainsString('streets', $response->headers->get('Location'));
        $response->assertSessionHas('success', 'Rue supprimée avec succès.');

        // Verify the street was deleted from the database
        $this->assertDatabaseMissing('streets', ['id' => $street->id]);
    }

    /**
     * Test validation for too long street name.
     */
    public function test_validation_rejects_too_long_name(): void
    {
        $admin = $this->createAdminUser();
        $sector = Sector::factory()->create();

        $streetData = [
            'sector_id' => $sector->id,
            'name' => str_repeat('a', 256), // Create a string longer than the max length of 255
            'postal_code' => $this->faker->postcode(),
            'city' => $this->faker->city(),
        ];

        $response = $this->actingAs($admin)
                         ->post(route('admin.streets.store'), $streetData);

        $response->assertSessionHasErrors('name');
    }

    /**
     * Test validation for too long postal code.
     */
    public function test_validation_rejects_too_long_postal_code(): void
    {
        $admin = $this->createAdminUser();
        $sector = Sector::factory()->create();

        $streetData = [
            'sector_id' => $sector->id,
            'name' => $this->faker->streetName(),
            'postal_code' => str_repeat('1', 11), // Create a string longer than the max length of 10
            'city' => $this->faker->city(),
        ];

        $response = $this->actingAs($admin)
                         ->from(route('admin.streets.create'))
                         ->post(route('admin.streets.store'), $streetData);

        $response->assertSessionHasErrors('postal_code');
    }

    /**
     * Test validation for too long city name.
     */
    public function test_validation_rejects_too_long_city(): void
    {
        $admin = $this->createAdminUser();
        $sector = Sector::factory()->create();

        $streetData = [
            'sector_id' => $sector->id,
            'name' => $this->faker->streetName(),
            'postal_code' => $this->faker->postcode(),
            'city' => str_repeat('a', 256), // Create a string longer than the max length of 255
        ];

        $response = $this->actingAs($admin)
                         ->from(route('admin.streets.create'))
                         ->post(route('admin.streets.store'), $streetData);

        $response->assertSessionHasErrors('city');
    }

    /**
     * Test createForSector method displays the form with selected sector.
     */
    public function test_create_for_sector_displays_form_with_selected_sector(): void
    {
        $admin = $this->createAdminUser();
        $sector = Sector::factory()->create();
        $otherSectors = Sector::factory()->count(2)->create();

        $response = $this->actingAs($admin)
                         ->get(route('admin.sectors.streets.create', $sector));

        $response->assertStatus(200);
        $response->assertViewIs('admin.streets.create');
        $response->assertViewHas('sectors');
        $response->assertViewHas('selected_sector');

        // Verify the selected sector is passed to the view
        $viewSelectedSector = $response->viewData('selected_sector');
        $this->assertEquals($sector->id, $viewSelectedSector->id);

        // Verify all sectors are passed to the view
        $viewSectors = $response->viewData('sectors');
        $this->assertCount(3, $viewSectors); // Our sector + 2 others
    }

    /**
     * Test that model binding works correctly.
     */
    public function test_model_binding_works_correctly(): void
    {
        $admin = $this->createAdminUser();
        $sector = Sector::factory()->create();
        $street = Street::factory()->create(['sector_id' => $sector->id]);

        // Test that the street is correctly bound for the show route
        $response = $this->actingAs($admin)
                         ->get(route('admin.streets.show', $street));

        $response->assertStatus(200);

        // Try to access a non-existent street
        $response = $this->actingAs($admin)
                         ->get(route('admin.streets.show', 999999));

        $response->assertStatus(404);
    }

    /**
     * Test storing a street with minimum required fields.
     */
    public function test_store_with_minimum_required_fields(): void
    {
        $admin = $this->createAdminUser();
        $sector = Sector::factory()->create();

        $streetData = [
            'sector_id' => $sector->id,
            'name' => $this->faker->streetName(),
            // Omitting optional fields: postal_code, city, notes
        ];

        $response = $this->actingAs($admin)
                         ->post(route('admin.streets.store'), $streetData);

        $response->assertRedirect();
        $response->assertSessionHas('success', 'Rue créée avec succès.');

        // Verify the street was created in the database with null values for optional fields
        $this->assertDatabaseHas('streets', [
            'sector_id' => $streetData['sector_id'],
            'name' => $streetData['name'],
        ]);

        // Get the street from the database and verify optional fields are null
        $createdStreet = Street::where('name', $streetData['name'])->first();
        $this->assertNull($createdStreet->postal_code);
        $this->assertNull($createdStreet->city);
        $this->assertNull($createdStreet->notes);
    }
}
