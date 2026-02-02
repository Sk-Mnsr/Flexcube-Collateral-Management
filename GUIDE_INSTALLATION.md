# Guide d'Installation et d'Exécution

## Application de Gestion des Garanties Bancaires

### Prérequis

- PHP >= 8.2
- Composer
- Node.js >= 18.x et npm
- MySQL ou PostgreSQL
- Serveur web (Apache/Nginx) ou PHP built-in server

---

## 📋 Étapes d'Installation

### 1. Installer les dépendances PHP

```bash
composer install
```

### 2. Installer les dépendances JavaScript

```bash
npm install
```

### 3. Configuration de l'environnement

Copiez le fichier `.env.example` vers `.env` (si ce n'est pas déjà fait) :

```bash
cp .env.example .env
```

Générez la clé d'application :

```bash
php artisan key:generate
```

Configurez votre base de données dans le fichier `.env` :

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=app_cof_garantie
DB_USERNAME=votre_utilisateur
DB_PASSWORD=votre_mot_de_passe
```

### 4. Créer la base de données

```bash
# MySQL
mysql -u root -p
CREATE DATABASE app_cof_garantie CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
exit;
```

### 5. Exécuter les migrations

```bash
php artisan migrate
```

Cette commande va créer toutes les tables nécessaires :
- `types_garanties`
- `garants`
- `garanties`
- `matricules_clients`
- `contrats_prets`
- `documentations_garanties`
- Et les tables pivot

### 6. Charger les données initiales (Seeders)

```bash
# Charger les types de garanties (12 types prédéfinis)
php artisan db:seed --class=TypeGarantieSeeder
```

### 7. Compiler les assets frontend

**Pour le développement :**
```bash
npm run dev
```

**Pour la production :**
```bash
npm run build
```

### 8. Démarrer le serveur

**Option 1 : Serveur de développement Laravel**
```bash
php artisan serve
```
L'application sera accessible sur `http://localhost:8000`

**Option 2 : Script tout-en-un (recommandé pour le développement)**
```bash
npm run dev:all
```
Ce script démarre automatiquement :
- Le serveur Laravel (`php artisan serve`)
- Le serveur Vite pour les assets frontend (`npm run dev`)
- Le système de logs (`php artisan pail`)
- Le worker de queue (`php artisan queue:listen`)

---

## 🚀 Démarrage Rapide

Pour démarrer rapidement l'application en mode développement :

```bash
# 1. Installer les dépendances (si pas déjà fait)
composer install
npm install

# 2. Configurer .env et générer la clé
php artisan key:generate

# 3. Migrer et seed la base de données
php artisan migrate
php artisan db:seed --class=TypeGarantieSeeder

# 4. Compiler les assets et démarrer le serveur
npm run dev:all
```

---

## 👤 Créer un utilisateur admin

Pour créer votre premier utilisateur administrateur :

```bash
php artisan tinker
```

Puis dans la console :

```php
$user = \App\Models\User::create([
    'name' => 'Admin',
    'email' => 'admin@example.com',
    'password' => bcrypt('password'),
    'profile' => 'admin',
    'activated' => true,
]);
```

Ou utilisez la route d'inscription si elle est activée.

---

## 📁 Structure du Projet

- **Backend (Laravel)** :
  - `app/Models/` - Modèles Eloquent
  - `app/Http/Controllers/` - Contrôleurs
  - `database/migrations/` - Migrations de base de données
  - `database/seeders/` - Seeders pour les données initiales

- **Frontend (Vue.js + Inertia)** :
  - `resources/js/pages/` - Pages Vue.js
  - `resources/js/components/` - Composants réutilisables
  - `resources/js/types/` - Définitions TypeScript

---

## 🔍 Vérification

Pour vérifier que tout fonctionne :

1. Accédez à `http://localhost:8000`
2. Connectez-vous avec vos identifiants
3. Vérifiez que les menus suivants sont visibles :
   - Garants
   - Garanties
   - Types de garanties
   - Contrats de prêts

---

## ⚙️ Commandes Utiles

```bash
# Voir les routes disponibles
php artisan route:list

# Vider le cache
php artisan cache:clear
php artisan config:clear
php artisan view:clear

# Régénérer les assets
npm run build

# Réinitialiser la base de données (ATTENTION : supprime toutes les données)
php artisan migrate:fresh --seed
```

---

## 🐛 Dépannage

**Erreur de connexion à la base de données :**
- Vérifiez les paramètres dans `.env`
- Assurez-vous que MySQL/PostgreSQL est démarré
- Vérifiez que la base de données existe

**Erreur 500 :**
- Exécutez `php artisan config:clear`
- Vérifiez les logs dans `storage/logs/laravel.log`

**Assets non chargés :**
- Exécutez `npm run build` ou `npm run dev`
- Videz le cache du navigateur

**Routes non trouvées :**
- Exécutez `php artisan route:clear`

---

## 📝 Notes

- En développement, utilisez `npm run dev` pour le hot-reload des composants Vue.js
- Les types de garanties sont chargés automatiquement via le seeder avec les valeurs du document Flexcube
- Les calculs de valeur réelle sont automatiques basés sur la décote et la pondération



