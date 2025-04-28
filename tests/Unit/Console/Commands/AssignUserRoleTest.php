<?php

namespace Tests\Unit\Console\Commands;

use App\Console\Commands\AssignUserRole;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Silber\Bouncer\BouncerFacade as Bouncer;
use Tests\TestCase;
use Illuminate\Support\Facades\DB;

class AssignUserRoleTest extends TestCase
{
    use RefreshDatabase;

    protected $command;

    public function setUp(): void
    {
        parent::setUp();
        
        // Initialize the Bouncer roles
        Bouncer::role()->firstOrCreate(['name' => 'user']);
        Bouncer::role()->firstOrCreate(['name' => 'admin']);
        $this->command = $this->app->make(AssignUserRole::class);
        
        // Clear Bouncer's cache
        Bouncer::refresh();
    }

    /**
     * Test assigning role to a specific user by email
     */
    public function test_assign_role_to_specific_user(): void
    {
        // Create a test user
        $user = User::factory()->create([
            'firstname' => 'John',
            'lastname' => 'Doe',
            'email' => 'john.doe@example.com'
        ]);

        // Make sure the user doesn't have the role yet
        $this->assertFalse($user->isA('user'));

        // Run the command with the user's email
        $this->artisan('user:assign-role', ['email' => 'john.doe@example.com'])
            ->expectsOutput("L'utilisateur John Doe (john.doe@example.com) a reçu le rôle 'user'.")
            ->assertExitCode(Command::SUCCESS);

        // Clear Bouncer's cache to refresh role assignments
        Bouncer::refresh();
        
        // Refresh user from database
        $user = User::where('email', 'john.doe@example.com')->first();

        // Check if the role was assigned
        $this->assertTrue($user->isA('user'));
    }

    /**
     * Test assigning role to all users without roles
     */
    public function test_assign_role_to_all_users(): void
    {
        // Create multiple test users without roles
        $users = User::factory(3)->create();
        
        // Create one user with admin role
        $adminUser = User::factory()->create();
        Bouncer::assign('admin')->to($adminUser);
        
        // Create one user with user role
        $userWithRole = User::factory()->create();
        Bouncer::assign('user')->to($userWithRole);

        // Run the command with --all option
        $this->artisan('user:assign-role', ['--all' => true])
            ->expectsOutput("3 utilisateurs ont reçu le rôle 'user'.")
            ->assertExitCode(Command::SUCCESS);

        // Clear Bouncer's cache to refresh role assignments
        Bouncer::refresh();
        
        // Reload users from database
        $userIds = $users->pluck('id')->toArray();
        $updatedUsers = User::whereIn('id', $userIds)->get();
        
        // Check if all users without roles got the 'user' role
        foreach ($updatedUsers as $user) {
            $this->assertTrue($user->isA('user'));
        }

        // The admin should not have the user role
        $adminUser = User::find($adminUser->id);
        $this->assertFalse($adminUser->isA('user'));
        $this->assertTrue($adminUser->isAn('admin'));

        // The user with role should still have it
        $userWithRole = User::find($userWithRole->id);
        $this->assertTrue($userWithRole->isA('user'));
    }

    /**
     * Test error when user email doesn't exist
     */
    public function test_error_when_user_not_found(): void
    {
        // Run the command with a non-existent email
        $this->artisan('user:assign-role', ['email' => 'nonexistent@example.com'])
            ->expectsOutput("Aucun utilisateur avec l'adresse email nonexistent@example.com n'a été trouvé.")
            ->assertExitCode(Command::FAILURE);
    }

    /**
     * Test error when no email or --all option is provided
     */
    public function test_error_when_no_params_provided(): void
    {
        // Run the command without any arguments or options
        $this->artisan('user:assign-role')
            ->expectsOutput("Veuillez spécifier une adresse email ou utiliser l'option --all.")
            ->assertExitCode(Command::FAILURE);
    }

    /**
     * Test informing when user already has the role
     */
    public function test_info_when_user_already_has_role(): void
    {
        // Create a test user with the role already assigned
        $user = User::factory()->create([
            'firstname' => 'Jane',
            'lastname' => 'Doe',
            'email' => 'jane.doe@example.com'
        ]);
        
        Bouncer::assign('user')->to($user);

        // Refresh Bouncer's cache to ensure role is recognized
        Bouncer::refresh();
        
        // Run the command with the user's email
        $this->artisan('user:assign-role', ['email' => 'jane.doe@example.com'])
            ->expectsOutput("L'utilisateur Jane Doe a déjà le rôle 'user'.")
            ->assertExitCode(Command::SUCCESS);

        // Refresh Bouncer's cache again
        Bouncer::refresh();
        
        // Verify the user still has the role
        $user = User::where('email', 'jane.doe@example.com')->first();
        $this->assertTrue($user->isA('user'));
    }
    
    /**
     * Test that users with both 'user' and 'admin' roles are not affected
     */
    public function test_users_with_both_roles_are_skipped(): void
    {
        // Create one user with both admin and user roles
        $userWithBothRoles = User::factory()->create([
            'firstname' => 'Admin',
            'lastname' => 'User',
            'email' => 'admin.user@example.com'
        ]);
        
        Bouncer::assign('admin')->to($userWithBothRoles);
        Bouncer::assign('user')->to($userWithBothRoles);
        
        // Create multiple test users without roles
        $users = User::factory(2)->create();
        
        // Refresh Bouncer's cache
        Bouncer::refresh();
        
        // Run the command with --all option
        $this->artisan('user:assign-role', ['--all' => true])
            ->expectsOutput("2 utilisateurs ont reçu le rôle 'user'.")
            ->assertExitCode(Command::SUCCESS);
        
        // Refresh Bouncer's cache
        Bouncer::refresh();
        
        // Verify the user still has both roles
        $userWithBothRoles = User::find($userWithBothRoles->id);
        $this->assertTrue($userWithBothRoles->isA('user'));
        $this->assertTrue($userWithBothRoles->isAn('admin'));
    }
}