<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_screen_can_be_rendered(): void
    {
        $response = $this->get('/register');

        $response->assertStatus(200);
    }

    public function test_new_users_can_register(): void
    {
        // Utiliser un mot de passe complexe et unique pour éviter l'erreur de compromission
        $response = $this->post('/register', [
            'firstname' => 'Test',
            'lastname' => 'User',
            'email' => 'test@example.com',
            'password' => 'P@ssw0rd'.time().'XyZ$!987',
            'password_confirmation' => 'P@ssw0rd'.time().'XyZ$!987',
        ]);

        // Vérifier la redirection vers la page de connexion plutôt que le tableau de bord
        // et ne pas vérifier l'authentification car l'utilisateur n'est pas connecté automatiquement
        // $this->assertAuthenticated();
        // Vérifions simplement qu'il y a une redirection
        $response->assertStatus(302);
        
        // Vérifions qu'un utilisateur a bien été créé avec les bonnes informations
        $this->assertDatabaseHas('users', [
            'firstname' => 'Test',
            'lastname' => 'User',
            'email' => 'test@example.com',
            'approved' => 0, // false est stocké comme 0 dans la base de données
        ]);
    }
}
