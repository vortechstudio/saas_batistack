# Batistack ERP

Batistack est un logiciel ERP SaaS développé par Vortechstudio, conçu pour la gestion commerciale et technique des entreprises du bâtiment. Il propose une interface moderne inspirée des sites EBP et OVH, et s'appuie sur Laravel, Livewire, FilamentPHP 4 et TailwindCSS.

## Fonctionnalités principales

- **Site vitrine public** : Présentation des fonctionnalités, avantages et offres Batistack, design inspiré de [ebp.com](https://www.ebp.com).
- **Espace client** : Accès sécurisé pour les clients, gestion des licences, factures, support technique, interface inspirée de l'espace membre OVH.
- **Interface administrateur** : Gestion commerciale et technique via FilamentPHP 4, réservée aux employés de Vortechstudio.

## Structure du projet

- `app/Models/` : Modèles Eloquent (Customer, Product, Feature, etc.)
- `database/migrations/` : Migrations pour la base de données
- `resources/views/` : Vues Blade et composants Livewire
- `resources/css/` : Styles TailwindCSS
- `resources/js/` : Scripts JS
- `routes/` : Fichiers de routes Laravel

## Technologies utilisées

- **Laravel 12**
- **Livewire** (Starter Kit, Flux, Volt)
- **FilamentPHP 4**
- **TailwindCSS**
- **Vite**

## Installation & démarrage

1. Cloner le dépôt :
   ```bash
   git clone https://github.com/vortechstudio/batistack.git
   ```
2. Installer les dépendances PHP :
   ```bash
   composer install
   ```
3. Installer les dépendances JS :
   ```bash
   npm install
   ```
4. Configurer l'environnement :
   - Copier `.env.example` vers `.env` et adapter les variables (base de données, mail, etc.)
5. Générer la clé d'application :
   ```bash
   php artisan key:generate
   ```
6. Lancer les migrations :
   ```bash
   php artisan migrate
   ```
7. Démarrer le serveur de développement :
   ```bash
   php artisan serve
   npm run dev
   ```

## Accès & authentification

- Authentification classique et double facteur (2FA)
- Possibilité de restreindre l'accès client par adresse IP
- Gestion des rôles (client, administrateur)

## Contribution

Les contributions sont les bienvenues ! Merci de suivre les conventions du projet et d'utiliser les outils fournis (Pest pour les tests, Pint pour le formatage).

## Licence

Ce projet est sous licence MIT.

---
Pour toute question ou support, contactez l'équipe Vortechstudio.