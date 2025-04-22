# PrefaCalendar - Gestion de Tournées de Calendriers pour Pompiers

PrefaCalendar est une application web développée avec Laravel 12 qui permet aux pompiers de gérer efficacement leurs tournées de distribution de calendriers. L'application facilite l'organisation des secteurs, des rues et des numéros de maisons à visiter, ainsi que le suivi des ventes et la collecte des fonds.

## Fonctionnalités

- **Système d'authentification et d'autorisation** : Utilisation de Laravel Breeze pour l'authentification et Bouncer pour la gestion des rôles et permissions
- **Gestion des utilisateurs** : Approbation des nouveaux utilisateurs par les administrateurs
- **Organisation géographique** :
  - Gestion des secteurs (zones géographiques)
  - Gestion des rues par secteur
  - Suivi des numéros de maisons à visiter
- **Gestion des tournées** :
  - Création et planification des tournées
  - Attribution des tournées aux utilisateurs
  - Suivi de l'état d'avancement des visites
  - Identification des maisons à revisiter
- **Bilan financier** :
  - Enregistrement détaillé des ventes (nombre de calendriers)
  - Comptabilisation des espèces (billets et pièces)
  - Suivi des chèques reçus
  - Calcul automatique des totaux

## Prérequis

- PHP 8.2 ou supérieur
- Composer
- Node.js et NPM
- Base de données (MySQL, PostgreSQL, SQLite)

## Installation

1. **Cloner le dépôt**

```bash
git clone <url-du-repository>
cd prefaCalendar
```

2. **Installer les dépendances PHP**

```bash
composer install
```

3. **Installer les dépendances JavaScript**

```bash
npm install
```

4. **Configurer l'environnement**

```bash
cp .env.example .env
php artisan key:generate
```

5. **Configurer la base de données**

Modifiez le fichier `.env` pour configurer l'accès à votre base de données :

```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=prefacalendar
DB_USERNAME=root
DB_PASSWORD=
```

6. **Exécuter les migrations et les seeders**

```bash
php artisan migrate --seed
```

7. **Compiler les assets**

```bash
npm run dev
```

8. **Démarrer le serveur de développement**

```bash
php artisan serve
```

Vous pouvez également utiliser la commande personnalisée définie dans `composer.json` :

```bash
composer dev
```

Cette commande exécute simultanément le serveur Laravel, la file d'attente, les logs et Vite.

## Structure des rôles et permissions

L'application utilise le package Bouncer pour gérer les rôles et les permissions :

- **Administrateurs** : Peuvent gérer les utilisateurs, les secteurs, les rues et toutes les tournées
- **Utilisateurs approuvés** : Peuvent créer et gérer leurs propres tournées et celles auxquelles ils sont assignés

## Workflow de l'application

1. Un administrateur crée des secteurs et des rues
2. Les utilisateurs créent des tournées pour des secteurs spécifiques
3. Les utilisateurs ajoutent des numéros de maisons à visiter pendant la tournée
4. Les utilisateurs mettent à jour le statut des visites (à revisiter, visité, ignoré)
5. À la fin de la tournée, les utilisateurs remplissent un formulaire détaillant les ventes et l'argent collecté
6. Le système génère automatiquement un bilan financier de la tournée

## Modèles de données

- **User** : Informations sur les utilisateurs et leurs rôles
- **Sector** : Zones géographiques
- **Street** : Rues appartenant à un secteur
- **Tour** : Tournées planifiées avec dates de début/fin et statut
- **HouseNumber** : Numéros de maisons à visiter pendant une tournée
- **TourCompletion** : Données financières et bilan de fin de tournée

## Développement

### Commandes utiles

- **Exécuter les tests** :
```bash
php artisan test
```

- **Lancer l'environnement de développement complet** :
```bash
composer dev
```

### Architecture du code

Le projet suit l'architecture MVC de Laravel :

- **Controllers** : Gèrent la logique métier
- **Models** : Définissent la structure des données et les relations
- **Views** : Interfaces utilisateur (Blade)
- **Routes** : Définition des points d'entrée de l'application
- **Middleware** : Contrôle des accès et vérifications

## Contribution

1. Créez une branche pour votre fonctionnalité
2. Committez vos changements
3. Soumettez une pull request

## License

Ce projet est sous licence MIT.
