# 🏗️ Batistack ERP

**Logiciel ERP SaaS spécialisé pour le secteur du BTP et de la construction**

[![Laravel](https://img.shields.io/badge/Laravel-12.x-red.svg)](https://laravel.com)
[![Livewire](https://img.shields.io/badge/Livewire-3.x-blue.svg)](https://livewire.laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.2+-purple.svg)](https://php.net)
[![License](https://img.shields.io/badge/License-MIT-green.svg)](LICENSE)

## 📋 Table des matières

- [À propos](#-à-propos)
- [Fonctionnalités](#-fonctionnalités)
- [Architecture technique](#-architecture-technique)
- [Installation](#-installation)
- [Configuration](#-configuration)
- [Utilisation](#-utilisation)
- [Modules disponibles](#-modules-disponibles)
- [Sécurité](#-sécurité)
- [API et intégrations](#-api-et-intégrations)
- [Déploiement](#-déploiement)
- [Support](#-support)
- [Licence](#-licence)

## 🎯 À propos

Batistack est une solution ERP SaaS moderne conçue spécifiquement pour les entreprises du secteur du BTP et de la construction. Notre plateforme offre une gestion complète des projets, des ressources humaines, de la comptabilité et des relations clients, adaptée aux besoins spécifiques de l'industrie de la construction.

### 🎯 Secteurs d'activité ciblés

- **Construction Durable** : Gestion des projets éco-responsables, suivi des certifications environnementales
- **Promotion Immobilière** : Gestion de portefeuille, suivi des ventes, relation investisseurs
- **Entreprises générales du BTP** : Coordination multi-corps d'état, planning de chantier
- **Artisans et PME** : Outils simplifiés pour la gestion quotidienne

## ✨ Fonctionnalités

### 🏢 Gestion d'entreprise
- **Gestion des projets** : Planification, suivi, budgétisation
- **Ressources humaines** : Gestion des équipes, planning, compétences
- **Comptabilité** : Facturation, devis, suivi financier
- **CRM** : Gestion clients, prospects, opportunités
- **Stocks et achats** : Inventaire, commandes, fournisseurs

### 📊 Modules spécialisés BTP
- **Planning de chantier** : Coordination des interventions
- **Gestion des matériaux** : Suivi des approvisionnements
- **Sécurité et conformité** : Respect des normes BTP
- **Sous-traitance** : Gestion des partenaires
- **Maintenance** : Suivi des équipements

### 🔧 Outils avancés
- **Rapports et analytics** : Tableaux de bord personnalisés
- **Intégrations** : APIs pour logiciels métier
- **Mobile** : Application responsive
- **Sauvegarde automatique** : Protection des données

## 🏗️ Architecture technique

### Stack technologique

- **Backend** : Laravel 12.x (PHP 8.2+)
- **Frontend** : Livewire 3.x + Flux UI
- **Base de données** : MySQL/PostgreSQL
- **Cache** : Redis
- **Queue** : Laravel Horizon
- **Paiements** : Stripe + Laravel Cashier
- **Interface admin** : Filament 4.x
- **UI Components** : Mary UI

### Dépendances principales

```json
{
  "laravel/framework": "^12.0",
  "livewire/livewire": "^3.6",
  "filament/filament": "~4.0",
  "laravel/cashier": "*",
  "spatie/laravel-permission": "^6.21",
  "spatie/laravel-activitylog": "^4.10",
  "pragmarx/google2fa": "^8.0"
}
```

### Structure de la base de données

#### Tables principales
- `users` : Utilisateurs et authentification
- `customers` : Clients et informations de facturation
- `products` : Offres et tarification
- `licenses` : Licences et domaines autorisés
- `modules` : Modules fonctionnels
- `options` : Options modulaires
- `subscriptions` : Abonnements Stripe

#### Tables de liaison
- `product_modules` : Association produits-modules
- `license_modules` : Modules activés par licence
- `product_options` : Options disponibles par produit

## 🚀 Installation

### Prérequis

- PHP 8.2 ou supérieur
- Composer
- Node.js et npm
- MySQL 8.0+ ou PostgreSQL 13+
- Redis (recommandé)

### Installation locale

```bash
# Cloner le repository
git clone https://github.com/votre-org/batistack-erp.git
cd batistack-erp

# Installer les dépendances PHP
composer install

# Installer les dépendances JavaScript
npm install

# Copier le fichier d'environnement
cp .env.example .env

# Générer la clé d'application
php artisan key:generate

# Configurer la base de données dans .env
# DB_CONNECTION=mysql
# DB_HOST=127.0.0.1
# DB_PORT=3306
# DB_DATABASE=batistack
# DB_USERNAME=root
# DB_PASSWORD=

# Exécuter les migrations
php artisan migrate

# Seeder les données de base
php artisan db:seed

# Compiler les assets
npm run build

# Démarrer le serveur de développement
php artisan serve
```

## ⚙️ Configuration

### Variables d'environnement essentielles

```env
# Application
APP_NAME="Batistack ERP"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://votre-domaine.com

# Base de données
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=batistack
DB_USERNAME=root
DB_PASSWORD=

# Redis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

# Stripe
STRIPE_KEY=pk_live_...
STRIPE_SECRET=sk_live_...
STRIPE_WEBHOOK_SECRET=whsec_...

# Mail
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=null
MAIL_PASSWORD=null
```

### Configuration Stripe

1. Créer un compte Stripe
2. Configurer les webhooks pour `/stripe/webhook`
3. Ajouter les clés dans `.env`
4. Créer les produits et prix dans Stripe

### Configuration des tâches cron

```bash
# Ajouter dans crontab
* * * * * cd /path/to/batistack && php artisan schedule:run >> /dev/null 2>&1
```

## 📖 Utilisation

### Interface d'administration

Accédez à `/admin` pour l'interface Filament :
- Gestion des utilisateurs et rôles
- Configuration des produits et modules
- Suivi des licences et abonnements
- Analytics et rapports

### Interface publique

- **Page d'accueil** : Présentation des offres
- **Solutions** : Détail des modules disponibles
- **Ressources** : Documentation et guides
- **Support** : Centre d'aide et contact

### Commandes Artisan utiles

```bash
# Gestion des licences
php artisan license:generate {customer_id} {product_id}
php artisan license:verify {license_key}
php artisan license:revoke {license_key}

# Synchronisation externe
php artisan sync:customers
php artisan sync:licenses

# Sauvegardes
php artisan backup:create --type=full
php artisan backup:cleanup --days=30

# Maintenance
php artisan horizon:work
php artisan queue:work
```

## 🧩 Modules disponibles

### Modules Essentiels
- **Gestion de Projet** : Planification et suivi
- **CRM** : Gestion des relations clients
- **Facturation** : Devis et factures
- **Comptabilité de Base** : Suivi financier
- **Gestion des Contacts** : Carnet d'adresses

### Modules Avancés
- **Planning Avancé** : Coordination multi-projets
- **Gestion des Stocks** : Inventaire et approvisionnement
- **RH Avancées** : Gestion complète du personnel
- **Rapports Personnalisés** : Analytics avancés
- **Intégrations API** : Connexions externes

### Modules Premium
- **BI et Analytics** : Intelligence d'affaires
- **Gestion Multi-sites** : Coordination géographique
- **Workflow Automation** : Automatisation des processus
- **Support Prioritaire** : Assistance dédiée
- **Formation Personnalisée** : Accompagnement sur mesure

## 🔒 Sécurité

### Authentification et autorisation
- **Authentification à deux facteurs (2FA)** : Google Authenticator
- **Gestion des rôles et permissions** : Spatie Laravel Permission
- **Audit des activités** : Traçabilité complète
- **Chiffrement des données sensibles** : Protection avancée

### Sécurité des licences
- **Vérification de domaine** : Contrôle d'accès par URL
- **Clés de licence uniques** : Génération sécurisée
- **Limitation d'utilisateurs** : Respect des quotas
- **Expiration automatique** : Gestion des échéances

### Conformité
- **RGPD** : Respect de la réglementation européenne
- **Sauvegarde automatique** : Protection contre la perte de données
- **Logs de sécurité** : Surveillance des accès

## 🔌 API et intégrations

### APIs disponibles
- **API REST** : Accès programmatique aux données
- **Webhooks** : Notifications en temps réel
- **API de licence** : Vérification et validation

### Intégrations tierces
- **Stripe** : Paiements et abonnements
- **Systèmes CRM** : Synchronisation des données
- **Logiciels comptables** : Export/import
- **Outils de communication** : Notifications

## 🚀 Déploiement

### Déploiement en production

```bash
# Optimisation pour la production
composer install --optimize-autoloader --no-dev
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Configuration du serveur web
# Voir PRODUCTION_DEPLOYMENT.md pour les détails
```

### Monitoring et logs
- **Laravel Horizon** : Surveillance des queues
- **Log Viewer** : Interface de consultation des logs
- **Métriques personnalisées** : Suivi des performances

## 📞 Support

### Offres de support

#### Support Standard (Starter)
- Documentation en ligne
- FAQ et guides
- Support par email (48h)

#### Support Professionnel (Professional)
- Support prioritaire (24h)
- Chat en direct
- Assistance téléphonique
- Formation de base

#### Support Enterprise (Enterprise)
- Support dédié (4h)
- Account manager
- Formation personnalisée
- Développements sur mesure

### Ressources
- **Documentation** : [docs.batistack.com](https://docs.batistack.com)
- **Centre d'aide** : [help.batistack.com](https://help.batistack.com)
- **Communauté** : [community.batistack.com](https://community.batistack.com)

## 📄 Licence

Ce projet est sous licence MIT. Voir le fichier [LICENSE](LICENSE) pour plus de détails.

---

**Développé avec ❤️ pour l'industrie du BTP**

*Batistack ERP - Construisons ensemble l'avenir du BTP numérique*