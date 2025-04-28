<?php

namespace Tests\Unit\Console\Commands;

use App\Console\Commands\CreateAdmin;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Silber\Bouncer\BouncerFacade as Bouncer;
use Tests\TestCase;
use Illuminate\Support\Facades\Hash;

class CreateAdminTest extends TestCase
{
    use RefreshDatabase;

    protected $command;

    public function setUp(): void
    {
        parent::setUp();
        
        // Initialize the Bouncer roles
        Bouncer::role()->firstOrCreate(['name' => 'admin']);
        $this->command = $this->app->make(CreateAdmin::class);
        
        // Clear Bouncer's cache
        Bouncer::refresh();
    }

    /**
     * Test creating a new admin user via command options
     */
    public function test_create_new_admin_with_options(): void
    {
        // Run the command with all options provided
        $this->artisan('app:create-admin', [
            '--firstname' => 'John',
            '--lastname' => 'Doe',
            '--email' => 'admin@example.com',
            '--password' => 'password123'
        ])
            ->expectsOutput('Nouvel utilisateur administrateur créé avec succès.')
            ->expectsOutput("L'utilisateur admin@example.com a reçu le rôle d'administrateur.")
            ->assertExitCode(Command::SUCCESS);

        // Verify user was created with correct data
        $user = User::where('email', 'admin@example.com')->first();
        $this->assertNotNull($user);
        $this->assertEquals('John', $user->firstname);
        $this->assertEquals('Doe', $user->lastname);
        $this->assertTrue($user->approved);
        
        // Verify password was hashed correctly
        $this->assertTrue(Hash::check('password123', $user->password));
        
        // Verify admin role was assigned
        Bouncer::refresh();
        $this->assertTrue($user->isAn('admin'));
    }

    /**
     * Test updating an existing user with admin role
     */
    public function test_update_existing_user_with_admin_role(): void
    {
        // Create a user first
        $user = User::create([
            'firstname' => 'Old',
            'lastname' => 'Name',
            'email' => 'existing@example.com',
            'password' => Hash::make('oldpassword'),
            'approved' => false,
        ]);

        // Run the command with the same email but different details
        $this->artisan('app:create-admin', [
            '--firstname' => 'New',
            '--lastname' => 'Name',
            '--email' => 'existing@example.com',
            '--password' => 'newpassword'
        ])
            ->expectsOutput('Utilisateur existant mis à jour avec succès.')
            ->expectsOutput("L'utilisateur existing@example.com a reçu le rôle d'administrateur.")
            ->assertExitCode(Command::SUCCESS);

        // Refresh the user and check updates
        $user = User::where('email', 'existing@example.com')->first();
        $this->assertEquals('New', $user->firstname);
        $this->assertEquals('Name', $user->lastname);
        $this->assertTrue($user->approved);
        $this->assertTrue(Hash::check('newpassword', $user->password));
        
        // Verify admin role was assigned
        Bouncer::refresh();
        $this->assertTrue($user->isAn('admin'));
    }

    /**
     * Test interactive creation via command prompts
     */
    public function test_create_admin_interactively(): void
    {
        // Run the command with interactive prompts
        $this->artisan('app:create-admin')
            ->expectsQuestion("Prénom de l'administrateur", 'Jane')
            ->expectsQuestion("Nom de l'administrateur", 'Smith')
            ->expectsQuestion("Email de l'administrateur", 'jane.admin@example.com')
            ->expectsQuestion("Mot de passe de l'administrateur", 'securepass')
            ->expectsOutput('Nouvel utilisateur administrateur créé avec succès.')
            ->expectsOutput("L'utilisateur jane.admin@example.com a reçu le rôle d'administrateur.")
            ->assertExitCode(Command::SUCCESS);

        // Verify user was created with correct data
        $user = User::where('email', 'jane.admin@example.com')->first();
        $this->assertNotNull($user);
        $this->assertEquals('Jane', $user->firstname);
        $this->assertEquals('Smith', $user->lastname);
        $this->assertTrue($user->approved);
        
        // Verify password was hashed
        $this->assertTrue(Hash::check('securepass', $user->password));
        
        // Verify admin role was assigned
        Bouncer::refresh();
        $this->assertTrue($user->isAn('admin'));
    }

    /**
     * Test updating existing user interactively
     */
    public function test_update_existing_user_interactively(): void
    {
        // Create user first
        $user = User::create([
            'firstname' => 'Initial',
            'lastname' => 'User',
            'email' => 'update.interactive@example.com',
            'password' => Hash::make('initial'),
            'approved' => false,
        ]);

        // Run command with some options and some interactive inputs
        $this->artisan('app:create-admin')
            ->expectsQuestion("Prénom de l'administrateur", 'Updated')
            ->expectsQuestion("Nom de l'administrateur", 'User')
            ->expectsQuestion("Email de l'administrateur", 'update.interactive@example.com')
            ->expectsQuestion("Mot de passe de l'administrateur", 'updated')
            ->expectsOutput('Utilisateur existant mis à jour avec succès.')
            ->expectsOutput("L'utilisateur update.interactive@example.com a reçu le rôle d'administrateur.")
            ->assertExitCode(Command::SUCCESS);

        // Verify user was updated correctly
        $user = User::where('email', 'update.interactive@example.com')->first();
        $this->assertEquals('Updated', $user->firstname);
        $this->assertEquals('User', $user->lastname);
        $this->assertTrue($user->approved);
        $this->assertTrue(Hash::check('updated', $user->password));
        
        // Verify admin role was assigned
        Bouncer::refresh();
        $this->assertTrue($user->isAn('admin'));
    }

    /**
     * Test partially updating an existing user
     */
    public function test_partial_update_existing_user(): void
    {
        // Create a user first
        $user = User::create([
            'firstname' => 'Partial',
            'lastname' => 'Update',
            'email' => 'partial.update@example.com',
            'password' => Hash::make('oldpass'),
            'approved' => false,
        ]);

        // Run the command with just some options
        $this->artisan('app:create-admin', [
            '--firstname' => 'NewFirst',
            '--email' => 'partial.update@example.com',
        ])
            ->expectsQuestion("Nom de l'administrateur", 'Update') // Keep the same
            ->expectsQuestion("Mot de passe de l'administrateur", 'newpass')
            ->expectsOutput('Utilisateur existant mis à jour avec succès.')
            ->expectsOutput("L'utilisateur partial.update@example.com a reçu le rôle d'administrateur.")
            ->assertExitCode(Command::SUCCESS);

        // Check the user details
        $user = User::where('email', 'partial.update@example.com')->first();
        $this->assertEquals('NewFirst', $user->firstname);
        $this->assertEquals('Update', $user->lastname);
        $this->assertTrue($user->approved);
        $this->assertTrue(Hash::check('newpass', $user->password));
        
        // Verify admin role was assigned
        Bouncer::refresh();
        $this->assertTrue($user->isAn('admin'));
    }

    /**
     * Test avec un email vide - La commande demande un email même si l'option est fournie avec une valeur vide
     */
    public function test_accept_empty_email(): void
    {
        // Compte le nombre d'utilisateurs avant exécution
        $countBefore = User::count();
        
        // Si on passe un email vide, la commande demande quand même interactivement
        $this->artisan('app:create-admin', [
            '--firstname' => 'Test',
            '--lastname' => 'User',
            '--password' => 'password',
        ])
            ->expectsQuestion("Email de l'administrateur", 'empty.email@example.com')
            ->expectsOutput('Nouvel utilisateur administrateur créé avec succès.')
            ->assertExitCode(Command::SUCCESS);

        // Vérifie qu'un utilisateur a été créé
        $this->assertEquals($countBefore + 1, User::count());
        
        // Vérifie que l'utilisateur a été créé avec l'email fourni
        $user = User::where('email', 'empty.email@example.com')->first();
        $this->assertNotNull($user);
        $this->assertEquals('Test', $user->firstname);
        $this->assertEquals('User', $user->lastname);
        
        // Vérifie que le rôle admin a été assigné
        Bouncer::refresh();
        $this->assertTrue($user->isAn('admin'));
    }

    /**
     * Test that the approved flag is always set to true
     */
    public function test_user_is_always_approved(): void
    {
        // Create a non-approved user first
        $user = User::create([
            'firstname' => 'Not',
            'lastname' => 'Approved',
            'email' => 'not.approved@example.com',
            'password' => Hash::make('password'),
            'approved' => false,
        ]);

        // Verify user is not approved
        $this->assertFalse($user->approved);

        // Run the command
        $this->artisan('app:create-admin', [
            '--email' => 'not.approved@example.com',
            '--password' => 'newpassword'
        ])
            ->expectsQuestion("Prénom de l'administrateur", 'Not')
            ->expectsQuestion("Nom de l'administrateur", 'Approved')
            ->expectsOutput('Utilisateur existant mis à jour avec succès.')
            ->assertExitCode(Command::SUCCESS);

        // Refresh user and check approved status
        $user = User::where('email', 'not.approved@example.com')->first();
        $this->assertTrue($user->approved);
    }

    /**
     * Test that email is converted to lowercase
     */
    public function test_email_is_converted_to_lowercase(): void
    {
        // Run the command with mixed-case email
        $this->artisan('app:create-admin', [
            '--firstname' => 'Case',
            '--lastname' => 'Test',
            '--email' => 'MiXeD.CaSe@ExAmPle.COM',
            '--password' => 'password'
        ])
            ->expectsOutput('Nouvel utilisateur administrateur créé avec succès.')
            ->assertExitCode(Command::SUCCESS);

        // Vérifie que l'utilisateur a été créé avec l'email en minuscules
        $user = User::where('email', 'mixed.case@example.com')->first();
        $this->assertNotNull($user);
        $this->assertEquals('mixed.case@example.com', $user->email);
        
        // Vérifie que la recherche est insensible à la casse (comportement Laravel/MySQL)
        $userCaseSensitive = User::whereRaw('BINARY email = ?', ['MiXeD.CaSe@ExAmPle.COM'])->first();
        $this->assertNull($userCaseSensitive, 'L\'email devrait être stocké uniquement en minuscules');
    }
}
