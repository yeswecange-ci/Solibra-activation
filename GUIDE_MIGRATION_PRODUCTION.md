# 🚨 Guide: Exécuter la Migration sur le Serveur de Production

## Problème Rencontré

```
SQLSTATE[42S22]: Column not found: 1054 Unknown column 'boisson_preferee' in 'field list'
```

**Cause**: La migration pour ajouter la colonne `boisson_preferee` n'a pas encore été exécutée sur le serveur de production.

**Serveur concerné**: `app-can-solibra.ywcdigital.com`

---

## ✅ Solution Temporaire Appliquée

Le code a été modifié pour **ne pas crasher** en attendant que la migration soit exécutée:
- Le filtre par boisson sera ignoré si la colonne n'existe pas
- La liste déroulante sera vide si la colonne n'existe pas
- **L'application fonctionne normalement** pour toutes les autres fonctionnalités

**Fichier modifié**: `app/Http/Controllers/Admin/UserController.php`
- Ajout de `try/catch` pour gérer l'absence de la colonne

---

## 🔧 Solution Définitive: Exécuter la Migration

### Option 1: Via SSH (Recommandé)

Si vous avez accès SSH au serveur:

```bash
# 1. Se connecter au serveur
ssh user@app-can-solibra.ywcdigital.com

# 2. Aller dans le répertoire de l'application
cd /var/www/votre-application  # Adapter le chemin

# 3. Exécuter la migration
php artisan migrate --force

# 4. Vérifier que la migration s'est bien passée
php artisan migrate:status

# 5. Vider le cache
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# 6. Redémarrer PHP-FPM (si applicable)
sudo systemctl restart php8.2-fpm  # Adapter la version de PHP
```

### Option 2: Via Panel d'Administration (cPanel, Plesk, etc.)

Si vous utilisez un panel:

1. **Ouvrir le Terminal** dans le panel
2. **Naviguer vers le dossier** de l'application:
   ```bash
   cd public_html  # ou le chemin de votre application
   ```

3. **Exécuter la migration**:
   ```bash
   php artisan migrate --force
   ```

4. **Vider le cache**:
   ```bash
   php artisan optimize:clear
   ```

### Option 3: Via FTP + Script PHP

Si vous n'avez pas d'accès SSH:

1. **Créer un fichier** `run_migration.php` à la racine:
   ```php
   <?php
   require __DIR__.'/vendor/autoload.php';
   $app = require_once __DIR__.'/bootstrap/app.php';
   $kernel = $app->make('Illuminate\Contracts\Console\Kernel');
   $kernel->bootstrap();

   // Exécuter la migration
   \Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
   echo "Migration exécutée:\n";
   echo \Illuminate\Support\Facades\Artisan::output();

   // Vider le cache
   \Illuminate\Support\Facades\Artisan::call('cache:clear');
   echo "\nCache vidé";
   ```

2. **Uploader le fichier** via FTP à la racine

3. **Accéder au fichier** via navigateur:
   ```
   https://app-can-solibra.ywcdigital.com/run_migration.php
   ```

4. **IMPORTANT**: **Supprimer le fichier** après exécution pour des raisons de sécurité

---

## 🧪 Vérification Après Migration

### 1. Vérifier la Colonne en Base de Données

**Via phpMyAdmin**:
```sql
DESCRIBE users;
```

**Résultat attendu**:
Vous devriez voir la colonne `boisson_preferee` dans la liste.

**Via MySQL CLI**:
```bash
mysql -u username -p
USE nom_de_la_base;
DESCRIBE users;
```

### 2. Vérifier l'Application

1. **Aller sur**: `https://app-can-solibra.ywcdigital.com/admin/users`
2. **Vérifier**:
   - ✅ La page charge sans erreur
   - ✅ Le filtre "Boisson préférée" apparaît
   - ✅ Les boissons s'affichent sous les noms des joueurs

### 3. Tester le Filtre

1. Si des utilisateurs ont déjà une boisson, sélectionner une boisson dans le filtre
2. Cliquer sur "Filtrer"
3. Vérifier que seuls les utilisateurs avec cette boisson s'affichent

---

## 📋 Checklist Complète

**Avant la Migration**:
- [ ] Backup de la base de données effectué
- [ ] Accès au serveur vérifié (SSH, Panel, ou FTP)
- [ ] Fichier de migration présent: `database/migrations/2025_12_24_000001_add_boisson_preferee_to_users_table.php`

**Pendant la Migration**:
- [ ] Connexion au serveur
- [ ] Navigation vers le dossier de l'application
- [ ] Exécution de `php artisan migrate --force`
- [ ] Vérification du message de succès

**Après la Migration**:
- [ ] Colonne `boisson_preferee` visible dans la table `users`
- [ ] Cache Laravel vidé
- [ ] Page `/admin/users` fonctionne sans erreur
- [ ] Filtre par boisson visible et fonctionnel
- [ ] Affichage des boissons sous les noms fonctionne

---

## 🔍 Fichier de Migration

**Emplacement**: `database/migrations/2025_12_24_000001_add_boisson_preferee_to_users_table.php`

**Contenu**:
```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('boisson_preferee')->nullable()->after('name');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('boisson_preferee');
        });
    }
};
```

**Ce que fait cette migration**:
- Ajoute une colonne `boisson_preferee` de type VARCHAR
- Colonne NULLABLE (peut être vide)
- Positionnée après la colonne `name`

---

## ⚠️ Problèmes Courants et Solutions

### Problème 1: "Nothing to migrate"

**Signification**: Les migrations ont déjà été exécutées (ou Laravel pense qu'elles l'ont été)

**Solution 1 - Vérifier l'état**:
```bash
php artisan migrate:status
```

**Solution 2 - Réinitialiser et relancer**:
```bash
# ATTENTION: Ceci supprime toutes les données !
# N'utilisez que si vous êtes sûr
php artisan migrate:fresh --force

# OU relancer la migration spécifique
php artisan migrate:rollback --step=1 --force
php artisan migrate --force
```

### Problème 2: Erreur "Access denied"

**Cause**: L'utilisateur de base de données n'a pas les droits

**Solution**: Vérifier le fichier `.env` sur le serveur:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=nom_base
DB_USERNAME=utilisateur
DB_PASSWORD=mot_de_passe
```

### Problème 3: Migration déjà exécutée mais colonne absente

**Cause**: La migration a été marquée comme exécutée mais a échoué

**Solution - Exécuter le SQL directement**:
```sql
ALTER TABLE users ADD COLUMN boisson_preferee VARCHAR(255) NULL AFTER name;
```

Puis marquer la migration comme exécutée:
```sql
INSERT INTO migrations (migration, batch)
VALUES ('2025_12_24_000001_add_boisson_preferee_to_users_table', 1);
```

---

## 🚀 Après la Migration Réussie

Une fois la migration exécutée:

1. **Déployer le code modifié** (avec les try/catch) sur le serveur
2. **Vider tous les caches**
3. **Tester l'application**
4. **Importer le flow Twilio** (`twilio_flow_with_boisson.json`)
5. **Tester le flow complet** avec un utilisateur réel

---

## 📞 Support

Si vous rencontrez des difficultés:

1. **Vérifier les logs Laravel**:
   ```bash
   tail -f storage/logs/laravel.log
   ```

2. **Vérifier les logs du serveur**:
   ```bash
   tail -f /var/log/nginx/error.log  # Nginx
   tail -f /var/log/apache2/error.log  # Apache
   ```

3. **Consulter la documentation Laravel**:
   - https://laravel.com/docs/migrations

---

## 📝 Commandes Utiles

```bash
# Voir l'état de toutes les migrations
php artisan migrate:status

# Exécuter les migrations en attente
php artisan migrate --force

# Rollback de la dernière migration
php artisan migrate:rollback --step=1 --force

# Vider tous les caches
php artisan optimize:clear

# Vérifier la configuration
php artisan config:show database

# Tester la connexion à la base de données
php artisan tinker
>>> DB::connection()->getPdo();
```

---

**Date**: 2024-12-24
**Serveur**: app-can-solibra.ywcdigital.com
**Migration**: 2025_12_24_000001_add_boisson_preferee_to_users_table
**Status**: ⏳ En attente d'exécution sur le serveur de production
