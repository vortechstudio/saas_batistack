# Batistack ERP

Batistack est un logiciel ERP SaaS développé par Vortechstudio, conçu pour la gestion commerciale et technique des entreprises du bâtiment. Il propose une interface moderne inspirée des sites EBP et OVH, et s'appuie sur Laravel, Livewire, FilamentPHP 4 et TailwindCSS.

## Fonctionnalités principales

### Interface Client
- **Site vitrine public** : Présentation des fonctionnalités, avantages et offres Batistack, design inspiré de [ebp.com](https://www.ebp.com)
- **Espace client sécurisé** : Interface inspirée de l'espace membre OVH avec :
  - Tableau de bord personnalisé
  - Gestion des commandes et facturation
  - Méthodes de paiement (intégration Stripe)
  - Gestion des services clients
  - Panier d'achat intégré

### Gestion des Services
- **Déploiement automatisé** : Création et configuration automatique des services clients
- **Gestion des domaines** : Intégration avec AaPanel pour la gestion DNS
- **Bases de données** : Création et gestion automatique des bases de données
- **Installation d'applications** : Déploiement automatique avec configuration SSH
- **Monitoring** : Vérification de la connectivité et des services essentiels
- **Gestion des licences** : Validation et activation des modules de licence

### Système de Paiement
- **Intégration Stripe** : Gestion complète des paiements, abonnements et remboursements
- **Facturation automatique** : Génération et envoi automatique des factures
- **Méthodes de paiement multiples** : Support de différents moyens de paiement
- **Gestion des commandes** : Suivi complet du cycle de vie des commandes

### Interface Administrateur
- **FilamentPHP 4** : Interface d'administration moderne et intuitive
- **Gestion commerciale** : Suivi des clients, commandes et revenus
- **Gestion technique** : Monitoring des services et infrastructure
- **Notifications** : Système de notifications intégré

## Architecture du projet

### Structure des dossiers
```
app/
├── Console/           # Commandes Artisan
├── Enum/             # Énumérations
├── Http/             # Contrôleurs et middlewares
├── Jobs/             # Tâches en arrière-plan
├── Livewire/         # Composants Livewire
├── Models/           # Modèles Eloquent
├── Notifications/    # Notifications
├── Providers/        # Fournisseurs de services
└── Services/         # Services métier
```

### Modèles principaux
- **Customer** : Gestion des clients avec relations vers les services, commandes et paiements
- **Order** : Système de commandes avec items, paiements et logs
- **Product** : Catalogue produits avec prix et fonctionnalités
- **CustomerService** : Services clients avec étapes d'installation et stockage
- **OrderPayment** : Gestion des paiements avec intégration Stripe

### Services et Jobs
- **Jobs d'installation** : `InitService`, `InitServiceSteps`, `InitDomain`
- **Jobs de vérification** : `VerifyServiceConnection`, `VerifyDomain`, `VerifyDatabase`
- **Jobs de déploiement** : `InstallMainApps`, `ActivateLicenseModule`
- **Services AaPanel** : Intégration complète pour la gestion des serveurs
- **Services Stripe** : Gestion des paiements et abonnements

### API et Routes
- **API REST** : Endpoints pour la validation de licences et informations
- **Routes Web** : Interface client complète avec authentification
- **Routes Auth** : Système d'authentification avec vérification email

### Base de données
- **Tables principales** : customers, orders, products, customer_services
- **Système de paiement** : order_payments, customer_payment_methods
- **Gestion des tâches** : jobs, job_batches, failed_jobs
- **Cache et sessions** : cache, sessions, notifications

## Technologies utilisées

- **Laravel 12** : Framework PHP moderne
- **Livewire** : Composants dynamiques (Starter Kit, Flux, Volt)
- **FilamentPHP 4** : Interface d'administration
- **TailwindCSS + DaisyUI** : Framework CSS utilitaire avec composants
- **Vite** : Build tool moderne
- **Stripe** : Plateforme de paiement
- **AaPanel API** : Gestion des serveurs et domaines

## Prérequis

- PHP 8.2 ou supérieur
- Composer
- Node.js et npm
- MySQL/MariaDB
- Redis (optionnel, pour le cache et les queues)

## Installation & configuration

### 1. Cloner le projet
```bash
git clone https://github.com/vortechstudio/batistack.git
cd batistack
```

### 2. Installation des dépendances
```bash
# Dépendances PHP
composer install

# Dépendances JavaScript
npm install
```

### 3. Configuration de l'environnement
```bash
# Copier le fichier d'environnement
cp .env.example .env

# Générer la clé d'application
php artisan key:generate
```

### 4. Configuration de la base de données
Éditer le fichier `.env` avec vos paramètres de base de données :
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=batistack
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

### 5. Configuration des services externes
```env
# Stripe
STRIPE_KEY=your_stripe_public_key
STRIPE_SECRET=your_stripe_secret_key
STRIPE_WEBHOOK_SECRET=your_webhook_secret

# AaPanel
AAPANEL_URL=your_aapanel_url
AAPANEL_KEY=your_aapanel_key

# Mail
MAIL_MAILER=smtp
MAIL_HOST=your_smtp_host
MAIL_PORT=587
MAIL_USERNAME=your_email
MAIL_PASSWORD=your_password
```

### 6. Initialisation de la base de données
```bash
# Exécuter les migrations
php artisan migrate

# (Optionnel) Seeder les données de test
php artisan db:seed
```

### 7. Démarrage du serveur de développement
```bash
# Terminal 1 : Serveur Laravel
php artisan serve

# Terminal 2 : Build des assets
npm run dev

# Terminal 3 : Worker pour les queues
php artisan queue:work
```

L'application sera accessible à l'adresse : `http://localhost:8000`

## Accès & authentification

- **Authentification sécurisée** : Système de connexion classique avec support 2FA
- **Restriction par IP** : Possibilité de limiter l'accès client par adresse IP
- **Gestion des rôles** : Séparation claire entre clients et administrateurs
- **Vérification email** : Processus de validation des adresses email
- **Réinitialisation de mot de passe** : Système sécurisé de récupération

## API et Intégrations

### API REST
- `GET /api/license/validate` : Validation des licences client
- `GET /api/license/info` : Informations détaillées sur les licences
- `GET /api/health` : Vérification de l'état de l'application

### Webhooks Stripe
Configuration automatique des webhooks pour :
- Gestion des paiements
- Mise à jour des abonnements
- Traitement des remboursements

### Intégration AaPanel
- Gestion automatique des domaines
- Configuration des bases de données
- Déploiement des applications
- Monitoring des services

## Développement

### Structure des tests
```bash
# Exécuter les tests
php artisan test

# Tests avec couverture
php artisan test --coverage
```

### Formatage du code
```bash
# Formatage avec Pint
./vendor/bin/pint

# Vérification du style
./vendor/bin/pint --test
```

### Queues et Jobs
Le système utilise des queues pour les tâches longues :
- Installation de services
- Envoi d'emails
- Synchronisation avec les APIs externes

```bash
# Démarrer le worker
php artisan queue:work

# Surveiller les queues
php artisan queue:monitor
```

## Contribution

Les contributions sont les bienvenues ! Merci de suivre les conventions du projet et d'utiliser les outils fournis (Pest pour les tests, Pint pour le formatage).

## Licence

Ce projet est sous licence MIT.

---
Pour toute question ou support, contactez l'équipe Vortechstudio.