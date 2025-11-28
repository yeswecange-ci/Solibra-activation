# 🔧 Fix: Vite Manifest Not Found

## Erreur rencontrée:
```
Illuminate\Foundation\ViteManifestNotFoundException
Vite manifest not found at: /app/public/build/manifest.json
```

## Solutions:

### ✅ Solution 1: Compiler les assets (RECOMMANDÉE)

Sur ton serveur de production, exécute :

```bash
# Se connecter au serveur
cd /app

# Installer les dépendances Node
npm install

# Compiler les assets pour production
npm run build
```

Cela va créer le fichier `public/build/manifest.json` et tous les assets compilés.

---

### ✅ Solution 2: Désactiver Vite temporairement

Si tu n'as pas besoin des assets JS/CSS pour le moment (pour tester l'API uniquement), tu peux désactiver temporairement Vite.

**Option A: Via variable d'environnement**

Ajoute dans ton `.env` :

```env
VITE_ENABLED=false
```

**Option B: Modifier les layouts Blade**

Dans `resources/views/admin/layouts/app.blade.php`, remplace :

```blade
@vite(['resources/css/app.css', 'resources/js/app.js'])
```

Par :

```blade
<!-- Temporairement désactivé pour test API -->
{{-- @vite(['resources/css/app.css', 'resources/js/app.js']) --}}
```

---

### ⚡ Solution Rapide (depuis ton PC local)

Si tu as accès SSH au serveur :

```bash
# 1. Sur ton PC local, compile les assets
npm run build

# 2. Upload le dossier public/build vers le serveur
scp -r public/build user@server:/app/public/

# Ou via FTP/SFTP, upload le dossier:
# Local: C:\YESWECANGE\can-activation-kinshasa\public\build
# Serveur: /app/public/build
```

---

## 🚀 Commandes de déploiement complètes

Pour un déploiement complet, exécute sur le serveur :

```bash
cd /app

# 1. Mettre à jour le code
git pull origin main

# 2. Installer dépendances PHP
composer install --optimize-autoloader --no-dev

# 3. Installer dépendances Node
npm install

# 4. Compiler assets
npm run build

# 5. Exécuter migrations
php artisan migrate --force

# 6. Optimiser Laravel
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 7. Donner permissions
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache

# 8. Redémarrer services
php artisan queue:restart
```

---

## ✅ Vérification

Après compilation, vérifie que ces fichiers existent sur le serveur :

```
/app/public/build/manifest.json
/app/public/build/assets/app-XXXXXXXX.css
/app/public/build/assets/app-XXXXXXXX.js
```

---

## 🔍 Debugging

Si l'erreur persiste, vérifie :

```bash
# 1. Permissions
ls -la /app/public/build/

# 2. Contenu du manifest
cat /app/public/build/manifest.json

# 3. Logs Laravel
tail -f /app/storage/logs/laravel.log
```
