#!/bin/bash

echo "Script d'installation pour prefaCalendar"
echo "========================================"

# Créer un fichier .env s'il n'existe pas
if [ ! -f .env ]; then
    echo "Création du fichier .env..."
    cp .env.example .env
    # Générer la clé d'application
    php artisan key:generate --no-interaction
fi

# Vérifier si SQLite est utilisé et créer le fichier de base de données si nécessaire
DB_CONNECTION=$(grep DB_CONNECTION .env | cut -d '=' -f 2)
if [ "$DB_CONNECTION" = "sqlite" ]; then
    echo "Configuration SQLite détectée"
    DB_DATABASE=$(grep DB_DATABASE .env | cut -d '=' -f 2)
    
    # Si le chemin n'est pas absolu, utiliser le répertoire database
    if [[ ! "$DB_DATABASE" =~ ^/ ]]; then
        echo "Chemin relatif détecté, création de la base de données dans database/"
        mkdir -p database
        touch database/database.sqlite
        # Mettre à jour le fichier .env
        sed -i "s|DB_DATABASE=.*|DB_DATABASE=$(pwd)/database/database.sqlite|" .env
    else
        # Créer le fichier de base de données à l'emplacement spécifié
        echo "Création de la base de données à $DB_DATABASE"
        mkdir -p $(dirname "$DB_DATABASE")
        touch "$DB_DATABASE"
    fi
fi

# Sauvegarder l'état actuel des fichiers pour restauration ultérieure
if [ ! -f app/Providers/AppServiceProvider.php.bak ]; then
    echo "Sauvegarde des fichiers originaux..."
    cp app/Providers/AppServiceProvider.php app/Providers/AppServiceProvider.php.bak
    cp app/Providers/BouncerServiceProvider.php app/Providers/BouncerServiceProvider.php.bak
    cp app/Providers/AuthServiceProvider.php app/Providers/AuthServiceProvider.php.bak
    cp app/Models/User.php app/Models/User.php.bak
fi

# Installer les dépendances
echo "Installation des dépendances..."
composer install --no-interaction

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

# Nettoyer le cache de l'application
echo "Nettoyage du cache..."
php artisan optimize:clear --no-interaction

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
        
        // Assigner le rôle admin
        \Silber\Bouncer\BouncerFacade::assign('admin')->to(\$user);
        
        echo 'Utilisateur administrateur créé avec succès!';
        echo 'Email: admin@example.com';
        echo 'Mot de passe: password';
    "
fi

echo ""
echo "Installation terminée!"
echo "Lancez l'application avec: php artisan serve"
