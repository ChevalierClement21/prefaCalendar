<?php

namespace Tests\Unit\Console\Commands;

use App\Console\Commands\AssignAdminRole;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Silber\Bouncer\BouncerFacade as Bouncer;
use Tests\TestCase;

class AssignAdminRoleTest extends TestCase
{
    use RefreshDatabase;

    protected $command;

    public function setUp(): void
    {
        parent::setUp();
        
        // Initialize the Bouncer roles and abilities
        Bouncer::role()->firstOrCreate(['name' => 'admin']);
        Bouncer::ability()->firstOrCreate(['name' => 'access-admin-panel']);
        Bouncer::ability()->firstOrCreate(['name' => 'manage-sectors']);
        Bouncer::ability()->firstOrCreate(['name' => 'manage-streets']);
        
        $this->command = $this->app->make(AssignAdminRole::class);
        
        // Clear Bouncer's cache
        Bouncer::refresh();
    }

    /**
     * Test assigning admin role to a user
     */
    public function test_assign_admin_role_to_user(): void
    {
        // Create a test user
        $user = User::factory()->create([
            'firstname' => 'John',
            'lastname' => 'Doe',
            'email' => 'john.doe@example.com',
        ]);

        // Make sure the user isn't an admin yet
        $this->assertFalse($user->isAn('admin'));
        $this->assertFalse($user->can('access-admin-panel'));
        $this->assertFalse($user->can('manage-sectors'));
        $this->assertFalse($user->can('manage-streets'));

        // Run the command
        $this->artisan('users:make-admin', ['email' => 'john.doe@example.com'])
            ->expectsOutput("L'utilisateur John Doe (john.doe@example.com) est maintenant administrateur.")
            ->assertExitCode(0);

        // Clear Bouncer's cache to refresh role assignments
        Bouncer::refresh();
        
        // Refresh user from database
        $user = User::where('email', 'john.doe@example.com')->first();

        // Check if the role and permissions were assigned
        $this->assertTrue($user->isAn('admin'));
        $this->assertTrue($user->can('access-admin-panel'));
        $this->assertTrue($user->can('manage-sectors'));
        $this->assertTrue($user->can('manage-streets'));
    }

    /**
     * Test assigning admin role to a user who already has it
     */
    public function test_assign_admin_role_to_existing_admin(): void
    {
        // Create a test user
        $user = User::factory()->create([
            'firstname' => 'Jane',
            'lastname' => 'Admin',
            'email' => 'jane.admin@example.com',
        ]);
        
        // Make user an admin manually
        Bouncer::assign('admin')->to($user);
        Bouncer::refresh();
        
        // Verify initial state
        $this->assertTrue($user->isAn('admin'));
        
        // Add only one permission
        Bouncer::allow($user)->to('access-admin-panel');
        Bouncer::refresh();
        
        $this->assertTrue($user->can('access-admin-panel'));
        $this->assertFalse($user->can('manage-sectors'));
        $this->assertFalse($user->can('manage-streets'));

        // Run the command
        $this->artisan('users:make-admin', ['email' => 'jane.admin@example.com'])
            ->expectsOutput("L'utilisateur Jane Admin (jane.admin@example.com) est maintenant administrateur.")
            ->assertExitCode(0);

        // Clear Bouncer's cache
        Bouncer::refresh();
        
        // Refresh user from database
        $user = User::where('email', 'jane.admin@example.com')->first();

        // User should still be admin and have all permissions now
        $this->assertTrue($user->isAn('admin'));
        $this->assertTrue($user->can('access-admin-panel'));
        $this->assertTrue($user->can('manage-sectors'));
        $this->assertTrue($user->can('manage-streets'));
    }

    /**
     * Test error when user email doesn't exist
     */
    public function test_error_when_user_not_found(): void
    {
        // Run the command with a non-existent email
        $this->artisan('users:make-admin', ['email' => 'nonexistent@example.com'])
            ->expectsOutput("Aucun utilisateur trouvé avec l'email: nonexistent@example.com")
            ->assertExitCode(1);
    }

    /**
     * Test case-insensitive email lookup
     */
    public function test_case_insensitive_email_lookup(): void
    {
        // Create a test user with lowercase email
        $user = User::factory()->create([
            'firstname' => 'Case',
            'lastname' => 'Test',
            'email' => 'case.test@example.com',
        ]);

        // Make sure the user isn't an admin yet
        $this->assertFalse($user->isAn('admin'));

        // Run the command with uppercase email
        $this->artisan('users:make-admin', ['email' => 'CASE.TEST@example.com'])
            ->expectsOutput("L'utilisateur Case Test (case.test@example.com) est maintenant administrateur.")
            ->assertExitCode(0);

        // Clear Bouncer's cache to refresh role assignments
        Bouncer::refresh();
        
        // Refresh user from database
        $user = User::where('email', 'case.test@example.com')->first();

        // Check if the role and permissions were assigned
        $this->assertTrue($user->isAn('admin'));
        $this->assertTrue($user->can('access-admin-panel'));
        $this->assertTrue($user->can('manage-sectors'));
        $this->assertTrue($user->can('manage-streets'));
    }
    
    /**
     * Test that permissions are correctly assigned
     */
    public function test_specific_permissions_assignment(): void
    {
        // Create a test user
        $user = User::factory()->create([
            'firstname' => 'Permissions',
            'lastname' => 'Test',
            'email' => 'permissions.test@example.com',
        ]);

        // Make sure the user has no permissions yet
        $this->assertFalse($user->can('access-admin-panel'));
        $this->assertFalse($user->can('manage-sectors'));
        $this->assertFalse($user->can('manage-streets'));

        // Run the command
        $this->artisan('users:make-admin', ['email' => 'permissions.test@example.com'])
            ->expectsOutput("L'utilisateur Permissions Test (permissions.test@example.com) est maintenant administrateur.")
            ->assertExitCode(0);

        // Clear Bouncer's cache to refresh role assignments
        Bouncer::refresh();
        
        // Refresh user from database
        $user = User::where('email', 'permissions.test@example.com')->first();

        // Individually check each permission
        $this->assertTrue($user->can('access-admin-panel'), "L'utilisateur devrait avoir la permission 'access-admin-panel'");
        $this->assertTrue($user->can('manage-sectors'), "L'utilisateur devrait avoir la permission 'manage-sectors'");
        $this->assertTrue($user->can('manage-streets'), "L'utilisateur devrait avoir la permission 'manage-streets'");
    }
}