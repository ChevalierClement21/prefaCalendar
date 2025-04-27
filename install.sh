#!/bin/bash

echo "Script d'installation pour prefaCalendar"
echo "========================================"

# Créer ou mettre à jour le fichier .env
echo "Configuration du fichier .env pour MariaDB..."
if [ ! -f .env ]; then
    cp .env.example .env
fi

# Configurer pour utiliser MariaDB
sed -i "s/DB_CONNECTION=.*/DB_CONNECTION=mysql/" .env
sed -i "s/DB_HOST=.*/DB_HOST=10.192.151.3/" .env
sed -i "s/DB_PORT=.*/DB_PORT=3306/" .env
sed -i "s/DB_DATABASE=.*/DB_DATABASE=prefaCalendar/" .env
sed -i "s/DB_USERNAME=.*/DB_USERNAME=prefauser/" .env
sed -i "s/DB_PASSWORD=.*/DB_PASSWORD=Not24get/" .env

# Générer la clé d'application si nécessaire
php artisan key:generate --no-interaction

# Sauvegarder l'état actuel des fichiers pour restauration ultérieure
if [ ! -f app/Providers/AppServiceProvider.php.bak ]; then
    echo "Sauvegarde des fichiers originaux..."
    cp app/Providers/AppServiceProvider.php app/Providers/AppServiceProvider.php.bak
    cp app/Providers/BouncerServiceProvider.php app/Providers/BouncerServiceProvider.php.bak
    cp app/Providers/AuthServiceProvider.php app/Providers/AuthServiceProvider.php.bak
    cp app/Models/User.php app/Models/User.php.bak
fi

# Désactiver temporairement Bouncer
echo "Désactivation temporaire de Bouncer pour l'installation..."

# 1. AppServiceProvider sans Bouncer
cat > app/Providers/AppServiceProvider.php << 'EOF'
<?php

namespace App\Providers;

use App\Models\Tour;
use App\Observers\TourObserver;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Enregistrer l'observateur de tournée
        Tour::observe(TourObserver::class);
        
        // Définir des autorisations accessibles à tous les utilisateurs authentifiés
        Gate::define('view-any-tours', function ($user) {
            return true;
        });
        
        Gate::define('create-tour', function ($user) {
            return true;
        });
        
        Gate::define('view-tour', function ($user, $tour) {
            return true;
        });
        
        Gate::define('update-tour', function ($user, $tour) {
            return true;
        });
        
        Gate::define('add-house-number', function ($user, $tour) {
            return true;
        });
        
        Gate::define('update-house-number-status', function ($user, $tour, $houseNumber) {
            return true;
        });
        
        Gate::define('complete-tour', function ($user, $tour) {
            return true;
        });

        Gate::define('approveUsers', function ($user) {
            return true;
        });
        
        Gate::define('manageRoles', function ($user) {
            return true;
        });
        
        Gate::define('manageSectors', function ($user) {
            return true;
        });
        
        Gate::define('manageStreets', function ($user) {
            return true;
        });
        
        Gate::define('manageSessions', function ($user) {
            return true;
        });
    }
}
EOF

# 2. BouncerServiceProvider vide
cat > app/Providers/BouncerServiceProvider.php << 'EOF'
<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class BouncerServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Désactivé pour l'installation
    }
}
EOF

# 3. AuthServiceProvider simplifié
cat > app/Providers/AuthServiceProvider.php << 'EOF'
<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [];

    public function boot(): void
    {
        Gate::define('admin', function ($user) {
            return true;
        });

        Gate::define('viewUsers', function ($user) {
            return true;
        });
    }
}
EOF

# 4. User modèle sans Bouncer
cat > app/Models/User.php << 'EOF'
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'firstname',
        'lastname',
        'email',
        'password',
        'approved',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'approved' => 'boolean',
        ];
    }
    
    public function tours(): BelongsToMany
    {
        return $this->belongsToMany(Tour::class, 'tour_user');
    }
    
    public function createdTours(): HasMany
    {
        return $this->hasMany(Tour::class, 'creator_id');
    }
    
    public function setEmailAttribute($value)
    {
        $this->attributes['email'] = strtolower($value);
    }
    
    public function isAn($role)
    {
        return true;
    }
    
    public function is($role)
    {
        return true;
    }
}
EOF

# Installer les dépendances
echo "Installation des dépendances..."
composer install --no-interaction

# Nettoyer le cache
echo "Nettoyage du cache..."
php artisan config:clear
php artisan cache:clear
php artisan optimize:clear

# Vérifier que la base de données est accessible
echo "Vérification de la connexion à la base de données..."
DB_CHECK=$(php artisan tinker --execute="try { DB::connection()->getPdo(); echo 'OK'; } catch (\Exception \$e) { echo \$e->getMessage(); }")

if [[ $DB_CHECK == *"OK"* ]]; then
    echo "Connexion à la base de données réussie."
else
    echo "ERREUR: Impossible de se connecter à la base de données. Message d'erreur:"
    echo "$DB_CHECK"
    echo "Veuillez vérifier vos paramètres de connexion et réessayer."
    exit 1
fi

# Exécuter les migrations
echo "Exécution des migrations..."
php artisan migrate --force --no-interaction

# Restaurer les fichiers originaux
echo "Restauration des fichiers originaux..."
if [ -f app/Providers/AppServiceProvider.php.bak ]; then
    cp app/Providers/AppServiceProvider.php.bak app/Providers/AppServiceProvider.php
    cp app/Providers/BouncerServiceProvider.php.bak app/Providers/BouncerServiceProvider.php
    cp app/Providers/AuthServiceProvider.php.bak app/Providers/AuthServiceProvider.php
    cp app/Models/User.php.bak app/Models/User.php
fi

# Nettoyer le cache à nouveau
echo "Nettoyage du cache..."
php artisan config:clear
php artisan cache:clear
php artisan optimize:clear

# Créer un utilisateur administrateur si nécessaire
echo "Voulez-vous créer un utilisateur administrateur ? (o/n)"
read -r create_admin

if [ "$create_admin" = "o" ] || [ "$create_admin" = "O" ]; then
    php artisan tinker --execute="
        \$user = new \App\Models\User();
        \$user->firstname = 'Admin';
        \$user->lastname = 'User';
        \$user->email = 'admin@example.com';
        \$user->password = bcrypt('password');
        \$user->approved = true;
        \$user->save();
        
        // Attribuer le rôle admin
        try {
            \Silber\Bouncer\BouncerFacade::assign('admin')->to(\$user);
            echo 'Rôle admin attribué avec succès.';
        } catch (\Exception \$e) {
            echo 'Erreur lors de l\'attribution du rôle admin: ' . \$e->getMessage();
        }
        
        echo 'Utilisateur administrateur créé avec succès!';
        echo 'Email: admin@example.com';
        echo 'Mot de passe: password';
    "
fi

echo ""
echo "Installation terminée!"
echo "Lancez l'application avec: php artisan serve"
