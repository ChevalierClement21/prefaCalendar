<?php

namespace Tests\Feature\Admin;

use App\Models\Sector;
use App\Models\Street;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Silber\Bouncer\BouncerFacade as Bouncer;
use Tests\TestCase;

class SectorControllerTest extends TestCase
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
     * Test the index method displays sectors correctly.
     */
    public function test_index_displays_sectors(): void
    {
        $admin = $this->createAdminUser();
        $sectors = Sector::factory()->count(3)->create();

        $response = $this->actingAs($admin)
                         ->get(route('admin.sectors.index'));

        $response->assertStatus(200);
        $response->assertViewIs('admin.sectors.index');
        $response->assertViewHas('sectors');

        // Verify that all sectors are passed to the view
        $viewSectors = $response->viewData('sectors');
        $this->assertCount(3, $viewSectors);

        // Verify the sectors are ordered by name
        $sortedSectors = $sectors->sortBy('name')->values();
        $this->assertEquals($sortedSectors->pluck('id')->toArray(), $viewSectors->pluck('id')->toArray());
    }

    /**
     * Test the create method displays the create form.
     */
    public function test_create_displays_form(): void
    {
        $admin = $this->createAdminUser();

        $response = $this->actingAs($admin)
                         ->get(route('admin.sectors.create'));

        $response->assertStatus(200);
        $response->assertViewIs('admin.sectors.create');
    }

    /**
     * Test the store method creates a new sector.
     */
    public function test_store_creates_new_sector(): void
    {
        $admin = $this->createAdminUser();

        $sectorData = [
            'name' => $this->faker->word(),
            'description' => $this->faker->sentence(),
            'color' => '#' . substr($this->faker->hexColor(), 0, 6), // Make sure it's not longer than 7 chars including #
        ];

        $response = $this->actingAs($admin)
                         ->post(route('admin.sectors.store'), $sectorData);

        // Check that we're redirected somewhere - the exact URL might vary
        $response->assertRedirect();
        // We can also check if the redirect URL contains 'sectors'
        $this->assertStringContainsString('sectors', $response->headers->get('Location'));
        $response->assertSessionHas('success', 'Secteur créé avec succès.');

        // Verify the sector was created in the database
        $this->assertDatabaseHas('sectors', [
            'name' => $sectorData['name'],
            'description' => $sectorData['description'],
            'color' => $sectorData['color'],
        ]);
    }

    /**
     * Test the store method validates the input.
     */
    public function test_store_validates_input(): void
    {
        $admin = $this->createAdminUser();

        // Try to create a sector without a name (required field)
        $response = $this->actingAs($admin)
                         ->post(route('admin.sectors.store'), [
                             'description' => $this->faker->sentence(),
                             'color' => '#' . $this->faker->hexColor(),
                         ]);

        $response->assertSessionHasErrors('name');
    }

    /**
     * Test the show method displays a sector correctly.
     */
    public function test_show_displays_sector(): void
    {
        $admin = $this->createAdminUser();
        $sector = Sector::factory()->create();
        $streets = Street::factory()->count(3)->create(['sector_id' => $sector->id]);

        $response = $this->actingAs($admin)
                         ->get(route('admin.sectors.show', $sector));

        $response->assertStatus(200);
        $response->assertViewIs('admin.sectors.show');
        $response->assertViewHas('sector');

        // Verify the sector is loaded with its streets
        $viewSector = $response->viewData('sector');
        $this->assertEquals($sector->id, $viewSector->id);
        $this->assertEquals(3, $viewSector->streets->count());
    }

    /**
     * Test the edit method displays the edit form.
     */
    public function test_edit_displays_form(): void
    {
        $admin = $this->createAdminUser();
        $sector = Sector::factory()->create();

        $response = $this->actingAs($admin)
                         ->get(route('admin.sectors.edit', $sector));

        $response->assertStatus(200);
        $response->assertViewIs('admin.sectors.edit');
        $response->assertViewHas('sector');

        // Verify the correct sector is passed to the view
        $viewSector = $response->viewData('sector');
        $this->assertEquals($sector->id, $viewSector->id);
    }

    /**
     * Test the update method updates a sector.
     */
    public function test_update_updates_sector(): void
    {
        $admin = $this->createAdminUser();
        $sector = Sector::factory()->create();

        $updatedData = [
            'name' => 'Updated Sector Name',
            'description' => 'Updated description',
            'color' => '#AABBCC', // Exactly 7 characters
        ];

        $response = $this->actingAs($admin)
                         ->put(route('admin.sectors.update', $sector), $updatedData);

        // Check that we're redirected somewhere related to sectors
        $response->assertRedirect();
        $this->assertStringContainsString('sectors', $response->headers->get('Location'));
        $response->assertSessionHas('success', 'Secteur mis à jour avec succès.');

        // Verify the sector was updated in the database
        $this->assertDatabaseHas('sectors', [
            'id' => $sector->id,
            'name' => $updatedData['name'],
            'description' => $updatedData['description'],
            'color' => $updatedData['color'],
        ]);
    }

    /**
     * Test the update method validates the input.
     */
    public function test_update_validates_input(): void
    {
        $admin = $this->createAdminUser();
        $sector = Sector::factory()->create();

        // Try to update a sector without a name (required field)
        $response = $this->actingAs($admin)
                         ->put(route('admin.sectors.update', $sector), [
                             'description' => 'Updated description',
                             'color' => '#AABBCC',
                         ]);

        $response->assertSessionHasErrors('name');
    }

    /**
     * Test the destroy method deletes a sector.
     */
    public function test_destroy_deletes_sector(): void
    {
        $admin = $this->createAdminUser();
        $sector = Sector::factory()->create();

        $response = $this->actingAs($admin)
                         ->delete(route('admin.sectors.destroy', $sector));

        $response->assertRedirect();
        $this->assertStringContainsString('sectors', $response->headers->get('Location'));
        $response->assertSessionHas('success', 'Secteur supprimé avec succès.');

        // Verify the sector was deleted from the database
        $this->assertDatabaseMissing('sectors', ['id' => $sector->id]);
    }

    /**
     * Test that related streets are deleted when a sector is deleted.
     */
    public function test_streets_are_deleted_with_sector(): void
    {
        $admin = $this->createAdminUser();
        $sector = Sector::factory()->create();
        $streets = Street::factory()->count(3)->create(['sector_id' => $sector->id]);

        $this->actingAs($admin)
             ->delete(route('admin.sectors.destroy', $sector));

        // Verify the sector was deleted from the database
        $this->assertDatabaseMissing('sectors', ['id' => $sector->id]);

        // Check if all related streets were deleted
        foreach ($streets as $street) {
            $this->assertDatabaseMissing('streets', ['id' => $street->id]);
        }
    }

    /**
     * Test validation for too long sector name.
     */
    public function test_validation_rejects_too_long_name(): void
    {
        $admin = $this->createAdminUser();

        $sectorData = [
            'name' => str_repeat('a', 256), // Create a string longer than the max length of 255
            'description' => $this->faker->sentence(),
            'color' => '#' . $this->faker->hexColor(),
        ];

        $response = $this->actingAs($admin)
                         ->post(route('admin.sectors.store'), $sectorData);

        $response->assertSessionHasErrors('name');
    }

    /**
     * Test validation for color field max length.
     */
    public function test_validation_rejects_too_long_color(): void
    {
        $admin = $this->createAdminUser();

        $sectorData = [
            'name' => $this->faker->word(),
            'description' => $this->faker->sentence(),
            'color' => '#' . str_repeat('A', 7),  // 8 characters (too long)
        ];

        // Make sure we're handling exceptions
        $this->withExceptionHandling();

        // Post the form to trigger validation
        $response = $this->actingAs($admin)
                         ->from(route('admin.sectors.create'))
                         ->post(route('admin.sectors.store'), $sectorData);

        // We expect to be redirected back to the form with errors
        $response->assertRedirect();

        // Check for specific error
        $response->assertInvalid(['color' => 'greater than']);
    }

    /**
     * Test that model binding works correctly.
     */
    public function test_model_binding_works_correctly(): void
    {
        $admin = $this->createAdminUser();
        $sector = Sector::factory()->create();

        // Test that the sector is correctly bound for the show route
        $response = $this->actingAs($admin)
                         ->get(route('admin.sectors.show', $sector));

        $response->assertStatus(200);

        // Try to access a non-existent sector
        $response = $this->actingAs($admin)
                         ->get(route('admin.sectors.show', 999999));

        $response->assertStatus(404);
    }

    /**
     * Test that color validation enforces the maximum length of 7 characters.
     * This is a redundant test with the one above, but uses a different color value.
     */
    public function test_color_validation_enforces_max_length(): void
    {
        $admin = $this->createAdminUser();

        $sectorData = [
            'name' => $this->faker->word(),
            'description' => $this->faker->sentence(),
            'color' => '#AABBCCD', // 8 characters (too long)
        ];

        // Make sure we're handling exceptions
        $this->withExceptionHandling();

        // Post the form to trigger validation
        $response = $this->actingAs($admin)
                         ->from(route('admin.sectors.create'))
                         ->post(route('admin.sectors.store'), $sectorData);

        // We expect to be redirected back to the form with errors
        $response->assertRedirect();

        // Check for specific error
        $response->assertInvalid(['color' => 'greater than']);
    }
}
