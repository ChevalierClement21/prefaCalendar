<?php

namespace Tests\Unit\Console\Commands;

use App\Console\Commands\FixAdminPermissions;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Silber\Bouncer\BouncerFacade as Bouncer;
use Tests\TestCase;

class FixAdminPermissionsTest extends TestCase
{
    use RefreshDatabase;

    protected $command;

    public function setUp(): void
    {
        parent::setUp();
        
        // Initialize the Bouncer roles
        Bouncer::role()->firstOrCreate(['name' => 'admin']);
        Bouncer::role()->firstOrCreate(['name' => 'user']);
        
        $this->command = $this->app->make(FixAdminPermissions::class);
        
        // Clear Bouncer's cache
        Bouncer::refresh();
    }

    /**
     * Test fixing permissions for a specific admin user by email.
     */
    public function test_fix_permissions_for_specific_admin(): void
    {
        // Create an admin user with 'approved' set to false
        $admin = User::factory()->create([
            'firstname' => 'Admin',
            'lastname' => 'User',
            'email' => 'admin@example.com',
            'approved' => false,
        ]);
        
        // Assign admin role
        Bouncer::assign('admin')->to($admin);
        Bouncer::refresh();
        
        // Verify user is an admin but not approved
        $this->assertTrue($admin->isAn('admin'));
        $this->assertFalse($admin->approved);
        
        // Run the command with the email parameter
        $this->artisan('admin:fix-permissions', ['email' => 'admin@example.com'])
            ->expectsOutput("Les autorisations de l'administrateur Admin User ont été réparées.")
            ->assertExitCode(Command::SUCCESS);
        
        // Reload the user from the database
        $admin->refresh();
        
        // Verify user is still an admin and now approved
        $this->assertTrue($admin->isAn('admin'));
        $this->assertTrue($admin->approved);
    }
    
    /**
     * Test fixing permissions for all admin users.
     */
    public function test_fix_permissions_for_all_admins(): void
    {
        // Create multiple admin users with 'approved' set to false
        $admin1 = User::factory()->create(['approved' => false]);
        $admin2 = User::factory()->create(['approved' => false]);
        $admin3 = User::factory()->create(['approved' => false]);
        
        // Create one non-admin user
        $regularUser = User::factory()->create(['approved' => false]);
        
        // Assign admin role to the admin users
        Bouncer::assign('admin')->to($admin1);
        Bouncer::assign('admin')->to($admin2);
        Bouncer::assign('admin')->to($admin3);
        Bouncer::assign('user')->to($regularUser);
        Bouncer::refresh();
        
        // Verify initial state
        $this->assertTrue($admin1->isAn('admin'));
        $this->assertTrue($admin2->isAn('admin'));
        $this->assertTrue($admin3->isAn('admin'));
        $this->assertFalse($admin1->approved);
        $this->assertFalse($admin2->approved);
        $this->assertFalse($admin3->approved);
        $this->assertFalse($regularUser->approved);
        
        // Run the command without any parameters (should fix all admins)
        $this->artisan('admin:fix-permissions')
            ->expectsOutput('Les autorisations de 3 administrateurs ont été réparées.')
            ->assertExitCode(Command::SUCCESS);
        
        // Reload users from the database
        $admin1->refresh();
        $admin2->refresh();
        $admin3->refresh();
        $regularUser->refresh();
        
        // Verify admins are now approved
        $this->assertTrue($admin1->approved);
        $this->assertTrue($admin2->approved);
        $this->assertTrue($admin3->approved);
        
        // Verify regular user is still not approved
        $this->assertFalse($regularUser->approved);
    }
    
    /**
     * Test command fails with non-existent email
     */
    public function test_command_fails_with_nonexistent_email(): void
    {
        $this->artisan('admin:fix-permissions', ['email' => 'nonexistent@example.com'])
            ->expectsOutput('Aucun utilisateur avec l\'adresse email nonexistent@example.com n\'a été trouvé.')
            ->assertExitCode(Command::FAILURE);
    }
    
    /**
     * Test command fails when user is not an admin
     */
    public function test_command_fails_when_user_is_not_admin(): void
    {
        // Create a non-admin user
        $user = User::factory()->create([
            'firstname' => 'Regular',
            'lastname' => 'User',
            'email' => 'regular@example.com',
            'approved' => false,
        ]);
        
        // Assign a regular user role
        Bouncer::assign('user')->to($user);
        Bouncer::refresh();
        
        // Run the command with a non-admin user
        $this->artisan('admin:fix-permissions', ['email' => 'regular@example.com'])
            ->expectsOutput('L\'utilisateur Regular User n\'est pas un administrateur.')
            ->assertExitCode(Command::FAILURE);
            
        // Verify user is still not approved
        $user->refresh();
        $this->assertFalse($user->approved);
    }
    
    /**
     * Test command fails when no admins exist
     */
    public function test_command_fails_when_no_admins_exist(): void
    {
        // Make sure there are no admin users in the database
        User::whereHas('roles', function ($query) {
            $query->where('name', 'admin');
        })->delete();
        
        // Create some regular users
        User::factory()->create(['approved' => false]);
        User::factory()->create(['approved' => false]);
        
        // Run the command without any parameters
        $this->artisan('admin:fix-permissions')
            ->expectsOutput('Aucun administrateur trouvé dans le système.')
            ->assertExitCode(Command::FAILURE);
    }
    
    /**
     * Test fixing a user that already has admin role and is already approved
     */
    public function test_fixing_already_approved_admin(): void
    {
        // Create an admin user that is already approved
        $admin = User::factory()->create([
            'firstname' => 'Approved',
            'lastname' => 'Admin',
            'email' => 'approved.admin@example.com',
            'approved' => true,
        ]);
        
        // Assign admin role
        Bouncer::assign('admin')->to($admin);
        Bouncer::refresh();
        
        // Run the command
        $this->artisan('admin:fix-permissions', ['email' => 'approved.admin@example.com'])
            ->expectsOutput('Les autorisations de l\'administrateur Approved Admin ont été réparées.')
            ->assertExitCode(Command::SUCCESS);
        
        // Verify status is unchanged
        $admin->refresh();
        $this->assertTrue($admin->isAn('admin'));
        $this->assertTrue($admin->approved);
    }
}
