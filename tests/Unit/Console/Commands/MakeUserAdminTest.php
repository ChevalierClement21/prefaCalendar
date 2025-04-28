<?php

namespace Tests\Unit\Console\Commands;

use App\Console\Commands\MakeUserAdmin;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Silber\Bouncer\BouncerFacade as Bouncer;
use Tests\TestCase;

class MakeUserAdminTest extends TestCase
{
    use RefreshDatabase;

    protected $command;

    public function setUp(): void
    {
        parent::setUp();
        
        // Initialize the Bouncer roles
        Bouncer::role()->firstOrCreate(['name' => 'admin']);
        $this->command = $this->app->make(MakeUserAdmin::class);
        
        // Clear Bouncer's cache
        Bouncer::refresh();
    }

    /**
     * Test making a user an admin
     */
    public function test_make_user_admin(): void
    {
        // Create a test user
        $user = User::factory()->create([
            'firstname' => 'John',
            'lastname' => 'Doe',
            'email' => 'john.doe@example.com',
            'approved' => false
        ]);

        // Make sure the user isn't an admin yet
        $this->assertFalse($user->isAn('admin'));
        $this->assertFalse($user->approved);

        // Run the command
        $this->artisan('user:make-admin', ['email' => 'john.doe@example.com'])
            ->expectsOutput("L'utilisateur John Doe (john.doe@example.com) est maintenant administrateur.")
            ->assertExitCode(Command::SUCCESS);

        // Clear Bouncer's cache to refresh role assignments
        Bouncer::refresh();
        
        // Refresh user from database
        $user = User::where('email', 'john.doe@example.com')->first();

        // Check if the role was assigned and user was approved
        $this->assertTrue($user->isAn('admin'));
        $this->assertTrue($user->approved);
    }

    /**
     * Test making an already approved user an admin
     */
    public function test_make_approved_user_admin(): void
    {
        // Create a test user who is already approved
        $user = User::factory()->create([
            'firstname' => 'Jane',
            'lastname' => 'Doe',
            'email' => 'jane.doe@example.com',
            'approved' => true
        ]);

        // Make sure the user isn't an admin yet
        $this->assertFalse($user->isAn('admin'));
        $this->assertTrue($user->approved);

        // Run the command
        $this->artisan('user:make-admin', ['email' => 'jane.doe@example.com'])
            ->expectsOutput("L'utilisateur Jane Doe (jane.doe@example.com) est maintenant administrateur.")
            ->assertExitCode(Command::SUCCESS);

        // Clear Bouncer's cache to refresh role assignments
        Bouncer::refresh();
        
        // Refresh user from database
        $user = User::where('email', 'jane.doe@example.com')->first();

        // Check if the role was assigned and user is still approved
        $this->assertTrue($user->isAn('admin'));
        $this->assertTrue($user->approved);
    }

    /**
     * Test making a user who is already an admin
     */
    public function test_user_already_admin(): void
    {
        // Create a test user
        $user = User::factory()->create([
            'firstname' => 'Admin',
            'lastname' => 'User',
            'email' => 'admin.user@example.com',
            'approved' => true
        ]);
        
        // Assign admin role
        Bouncer::assign('admin')->to($user);
        
        // Refresh Bouncer's cache
        Bouncer::refresh();
        
        // Verify user is already an admin
        $this->assertTrue($user->isAn('admin'));

        // Run the command again
        $this->artisan('user:make-admin', ['email' => 'admin.user@example.com'])
            ->expectsOutput("L'utilisateur Admin User (admin.user@example.com) est maintenant administrateur.")
            ->assertExitCode(Command::SUCCESS);

        // Refresh Bouncer's cache
        Bouncer::refresh();
        
        // User should still be an admin
        $user = User::where('email', 'admin.user@example.com')->first();
        $this->assertTrue($user->isAn('admin'));
    }

    /**
     * Test error when user email doesn't exist
     */
    public function test_error_when_user_not_found(): void
    {
        // Run the command with a non-existent email
        $this->artisan('user:make-admin', ['email' => 'nonexistent@example.com'])
            ->expectsOutput("Aucun utilisateur avec l'adresse email nonexistent@example.com n'a été trouvé.")
            ->assertExitCode(Command::FAILURE);
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
            'approved' => false
        ]);

        // Make sure the user isn't an admin yet
        $this->assertFalse($user->isAn('admin'));

        // Run the command with uppercase email
        $this->artisan('user:make-admin', ['email' => 'CASE.TEST@example.com'])
            ->expectsOutput("L'utilisateur Case Test (case.test@example.com) est maintenant administrateur.")
            ->assertExitCode(Command::SUCCESS);

        // Clear Bouncer's cache to refresh role assignments
        Bouncer::refresh();
        
        // Refresh user from database
        $user = User::where('email', 'case.test@example.com')->first();

        // Check if the role was assigned
        $this->assertTrue($user->isAn('admin'));
        $this->assertTrue($user->approved);
    }
}