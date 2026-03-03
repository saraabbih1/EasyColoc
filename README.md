# EasyColoc - Gestion de Colocation

<p align="center">
    <img src="https://img.shields.io/badge/Laravel-12.0-FF2D20?style=for-the-badge&logo=laravel&logoColor=white" alt="Laravel">
    <img src="https://img.shields.io/badge/Tailwind%20CSS-3.4.0-38B2AC?style=for-the-badge&logo=tailwind-css&logoColor=white" alt="Tailwind CSS">
    <img src="https://img.shields.io/badge/PHP-8.2-777BB4?style=for-the-badge&logo=php&logoColor=white" alt="PHP">
    <img src="https://img.shields.io/badge/MySQL-8.0-4479A1?style=for-the-badge&logo=mysql&logoColor=white" alt="MySQL">
</p>

EasyColoc est une application web Laravel complète pour la gestion de colocations et le suivi des dépenses communes. Permet aux utilisateurs de savoir qui doit quoi à qui, avec calcul automatique des soldes, gestion des invitations, des rôles, et un système de réputation.

##  Fonctionnalités principales

###  Gestion des utilisateurs
- **Inscription / connexion** avec Laravel Breeze
- **Rôles et permissions** : Global Admin, Owner, Member
- **Système de réputation** : +1/-1 selon le comportement
- **Bannissement automatique** pour les utilisateurs bannis
- **Premier inscrit devient Global Admin**

###  Gestion des colocations
- **Création de colocation** avec owner automatique
- **Invitation par email/token** avec acceptation/refus
- **Une seule colocation active** par utilisateur
- **Gestion des membres** (ajout, retrait, départ)
- **Annulation de colocation** avec gestion des dettes

###  Suivi des dépenses
- **Ajout de dépenses** avec titre, montant, date, catégorie, payeur
- **Historique complet** des dépenses
- **Statistiques par catégorie** et mensuelles
- **Filtrage par mois** et par catégorie
- **Calcul automatique** des parts individuelles

###  Gestion des dettes
- **Calcul automatique** : total payé, part individuelle, solde
- **Vue synthétique** "qui doit à qui"
- **Marquage des paiements** pour réduire les dettes
- **Optimisation des dettes** pour minimiser les transactions

###  Interface moderne
- **Responsive Design** avec Tailwind CSS
- **Interface intuitive** et ergonomique
- **Dashboard personnalisé** selon le rôle
- **Notifications et messages** informatifs

##  Architecture technique

- **Framework** : Laravel 12.0 (dernier stable)
- **Architecture** : MVC monolithique
- **Base de données** : MySQL avec migrations Laravel
- **ORM** : Eloquent avec relations hasMany et belongsToMany
- **Authentification** : Laravel Breeze
- **Front-end** : Blade + Tailwind CSS + JavaScript natif
- **Sécurité** : CSRF, validation Form Requests, protection XSS

##  Prérequis

- PHP 8.2 ou supérieur
- Composer 2.0 ou supérieur
- MySQL 8.0 ou supérieur
- Node.js 18+ et NPM (pour les assets)

##  Installation

### 1. Cloner le projet
```bash
git clone <repository-url>
cd easy-coloc
```

### 2. Installer les dépendances
```bash
composer install
npm install
```

### 3. Configuration de l'environnement
```bash
cp .env.example .env
php artisan key:generate
```

### 4. Configurer la base de données
Éditez le fichier `.env` avec vos informations de base de données :
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=easycoloc
DB_USERNAME=root
DB_PASSWORD=votre_mot_de_passe
```

### 5. Exécuter les migrations et les seeders
```bash
php artisan migrate --seed
```

### 6. Compiler les assets
```bash
npm run build
```

### 7. Démarrer le serveur
```bash
php artisan serve
```

L'application sera disponible sur `http://localhost:8000`

## Comptes de démonstration

Après l'installation avec les seeders, vous pouvez utiliser ces comptes :

| Email | Mot de passe | Rôle | Description |
|-------|-------------|------|-------------|
| admin@easycoloc.test | password | Admin | Administrateur global |
| alice@easycoloc.test | password | User | Propriétaire d'une colocation |
| bob@easycoloc.test | password | User | Membre d'une colocation |
| carla@easycoloc.test | password | User | Membre d'une colocation |
| david@easycoloc.test | password | User | Propriétaire d'une autre colocation |
| banned@easycoloc.test | password | User | Compte banni (test) |

##  Structure de la base de données

### Tables principales
- **users** : Informations utilisateurs avec rôle, réputation, statut banni
- **colocations** : Informations des colocations
- **memberships** : Table pivot entre users et colocations (rôle, statut)
- **expenses** : Dépenses avec catégorie, payeur, date
- **categories** : Catégories de dépenses par colocation
- **settlements** : Dettes entre utilisateurs
- **invitations** : Invitations en attente avec token

### Relations clés
- User ↔ Colocation : belongsToMany via memberships
- Colocation ↔ Expense : hasMany
- Expense ↔ User : belongsTo (payeur)
- Expense ↔ Category : belongsTo
- Settlement : relations debtor et creditor avec User

##  Sécurité

- **Protection CSRF** sur toutes les routes sensibles
- **Validation côté serveur** avec Form Requests
- **Protection XSS** automatique avec Blade
- **Middleware personnalisés** : `not.banned`, `admin`
- **Gates et Policies** pour les autorisations fines
- **Hashage des mots de passe** avec bcrypt

## Cas d'utilisation

### Scénario 1 : Création de colocation
1. Alice crée une colocation "Appartement Paris 15"
2. Elle devient automatiquement owner
3. Elle invite Bob par email
4. Bob accepte l'invitation et devient membre

### Scénario 2 : Gestion des dépenses
1. Alice fait des courses pour 150€
2. Elle enregistre la dépense dans l'application
3. Le système calcule automatiquement : Alice +100€, Bob -50€, Carla -50€
4. Bob et Carla voient leurs dettes dans l'interface

### Scénario 3 : Optimisation des dettes
1. Plusieurs dépenses créent des dettes croisées
2. Alice clique sur "Optimiser les dettes"
3. Le système réduit le nombre de transactions nécessaires
4. Moins de virements à effectuer

##  Tests et développement

### Lancer les tests
```bash
php artisan test
```

### Fresh installation (développement)
```bash
php artisan migrate:fresh --seed
npm run dev
```

### Génération de données de test
```bash
php artisan tinker
User::factory()->count(50)->create();
```

##  Statistiques et monitoring

### Dashboard Admin
- **Utilisateurs** : total, actifs, bannis
- **Colocations** : actives, annulées
- **Dépenses** : total, montant moyen
- **Graphiques** : registrations mensuelles, dépenses mensuelles

### Dashboard Utilisateur
- **Solde personnel** : positif (créditeur) ou négatif (débiteur)
- **Dépenses récentes** de la colocation
- **Membres** actifs et leur statut
- **Invitations** en attente

##  Workflow de développement

1. **Fork** le projet
2. **Créer une branche** feature/nom-de-la-fonctionnalité
3. **Développer** en respectant les standards Laravel
4. **Tester** avec PHPUnit
5. **Commit** avec messages clairs
6. **Push** et créer une **Pull Request**

##  Notes importantes

- **Premier utilisateur** devient automatiquement Global Admin
- **Un utilisateur** ne peut avoir qu'une seule colocation active
- **Maximum 10 membres** par colocation
- **Invitations** expirent après 7 jours
- **Réputation** ajustée automatiquement lors des départs/annulations

##  Contribuer

Les contributions sont bienvenues ! Veuillez :

1. Respecter le code style PSR-12
2. Ajouter des tests pour les nouvelles fonctionnalités
3. Documenter les changements dans le README
4. Utiliser des messages de commit clairs

##  Licence

Ce projet est sous licence MIT. Voir le fichier `LICENSE` pour plus de détails.

##  Support

Pour toute question ou problème :

- Créer une **issue** sur GitHub
- Contacter le mainteneur du projet
- Consulter la documentation Laravel officielle

---

**EasyColoc** - Simplifiez la gestion de votre colocation ! 
