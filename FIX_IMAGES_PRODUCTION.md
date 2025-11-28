# 🖼️ Fix Images Partenaires - Production

## 🎯 Problème
Les logos des partenaires ne s'affichent pas sur https://wabracongo.ywcdigital.com

## 🔍 Diagnostic

Les images sont stockées dans :
```
storage/app/public/partners/logos/nom-fichier.jpg
```

Mais accessibles via :
```
public/storage/partners/logos/nom-fichier.jpg
```

**Problèmes identifiés :**
1. ❌ Lien symbolique `public/storage` manquant
2. ❌ URLs générées en HTTP au lieu de HTTPS (déjà corrigé avec le fix précédent)
3. ⚠️ Permissions potentiellement incorrectes

---

## ✅ Solutions

### 1. Créer le Lien Symbolique

**Sur le serveur de production (Coolify) :**

```bash
# Se connecter au terminal Coolify
# Puis exécuter :
php artisan storage:link
```

**Résultat attendu :**
```
The [public/storage] link has been connected to [storage/app/public].
The links have been created.
```

Cela crée un lien symbolique :
```
public/storage → storage/app/public
```

---

### 2. Vérifier les Permissions

```bash
# Dans le terminal Coolify
chmod -R 755 storage/app/public
chmod -R 755 public/storage
chown -R www-data:www-data storage/app/public
```

---

### 3. Automatiser avec le Déploiement Coolify

Pour que le lien symbolique soit recréé à chaque déploiement, ajoute `php artisan storage:link` aux commandes de build.

**Dans les paramètres Coolify :**

**Build Command :**
```bash
composer install --optimize-autoload --no-dev
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan storage:link
```

**Ou dans un fichier de démarrage :**

Créer un fichier `docker-entrypoint.sh` :
```bash
#!/bin/bash

# Créer le lien symbolique storage
php artisan storage:link

# Autres commandes...
exec "$@"
```

---

## 🧪 Test

### 1. Vérifier que le lien existe

```bash
# Dans le terminal Coolify
ls -la public/storage

# Résultat attendu :
# lrwxrwxrwx 1 www-data www-data 25 Nov 28 12:00 public/storage -> ../storage/app/public
```

### 2. Vérifier qu'une image existe

```bash
# Lister les logos uploadés
ls -la storage/app/public/partners/logos/

# Exemple de résultat :
# -rw-r--r-- 1 www-data www-data 45678 Nov 28 12:00 bracongo-logo.jpg
```

### 3. Tester l'accès HTTP

```bash
# Remplace par le nom réel de ton fichier
curl -I https://wabracongo.ywcdigital.com/storage/partners/logos/bracongo-logo.jpg
```

**Résultat attendu :**
```
HTTP/1.1 200 OK
Content-Type: image/jpeg
Content-Length: 45678
```

### 4. Vérifier dans le navigateur

1. Va sur https://wabracongo.ywcdigital.com/admin/partners
2. Les logos doivent s'afficher dans la colonne "Logo"
3. Ouvre la console (F12) → Onglet "Network"
4. Filtre par "Img"
5. Vérifie qu'il n'y a pas d'erreurs 404

---

## 🔧 Alternative : Utiliser un Disk Différent

Si le problème persiste avec le lien symbolique, tu peux stocker les images directement dans `public/` :

**Modifier `config/filesystems.php` :**

```php
'disks' => [
    // ...

    'partners' => [
        'driver' => 'local',
        'root' => public_path('uploads/partners'),
        'url' => env('APP_URL').'/uploads/partners',
        'visibility' => 'public',
    ],
],
```

**Modifier `PartnerController.php` :**

```php
// Ligne 37 : Changer 'public' en 'partners'
$validated['logo'] = $request->file('logo')->store('logos', 'partners');

// Ligne 72 : Changer 'public' en 'partners'
Storage::disk('partners')->delete($partner->logo);

// Ligne 74 : Changer 'public' en 'partners'
$validated['logo'] = $request->file('logo')->store('logos', 'partners');
```

**Modifier les vues (index.blade.php, edit.blade.php, show.blade.php) :**

```php
<!-- Avant -->
<img src="{{ asset('storage/' . $partner->logo) }}" ...>

<!-- Après -->
<img src="{{ asset('uploads/partners/' . $partner->logo) }}" ...>
```

**Avantage :** Pas besoin de lien symbolique, les images sont directement dans `public/uploads/partners/`

---

## 🐛 Troubleshooting

### Problème 1 : "The link already exists"

Si `php artisan storage:link` retourne cette erreur :

```bash
# Supprimer le lien existant
rm public/storage

# Recréer le lien
php artisan storage:link
```

### Problème 2 : Permissions refusées

```bash
# Donner les permissions à Laravel
sudo chown -R www-data:www-data storage/
sudo chown -R www-data:www-data public/
sudo chmod -R 755 storage/
sudo chmod -R 755 public/
```

### Problème 3 : 404 sur les images

**Vérifier que les images existent :**
```bash
find storage/app/public -name "*.jpg" -o -name "*.png"
```

**Vérifier le chemin dans la base de données :**
```bash
php artisan tinker
>>> \App\Models\Partner::all()->pluck('logo')
```

**Résultat attendu :**
```
[
  "partners/logos/bracongo-logo.jpg",
  "partners/logos/vodacom-logo.png",
]
```

### Problème 4 : Images en HTTP bloquées par le navigateur

C'est déjà corrigé par le fix `URL::forceScheme('https')` dans `AppServiceProvider.php`.

Vérifie que le code a bien été redéployé :
```bash
# Dans le terminal Coolify
grep -n "forceScheme" app/Providers/AppServiceProvider.php
```

**Résultat attendu :**
```php
26:            URL::forceScheme('https');
```

---

## ✅ Checklist Finale

Après avoir appliqué le fix :

- [ ] ✅ `php artisan storage:link` exécuté sur le serveur
- [ ] ✅ Lien symbolique `public/storage` existe
- [ ] ✅ Permissions 755 sur `storage/app/public`
- [ ] ✅ Test curl retourne 200 OK
- [ ] ✅ Images s'affichent dans l'admin
- [ ] ✅ URLs des images en HTTPS (pas d'erreurs Mixed Content)
- [ ] ✅ Commande `storage:link` ajoutée au script de déploiement

---

## 📊 Structure des Fichiers

**Après le fix, voici la structure attendue :**

```
projet/
├── public/
│   ├── storage/                    ← LIEN SYMBOLIQUE
│   │   └── partners/
│   │       └── logos/
│   │           ├── bracongo.jpg    ← Accessible via /storage/partners/logos/bracongo.jpg
│   │           └── vodacom.png
│   └── index.php
│
└── storage/
    └── app/
        └── public/                 ← DOSSIER RÉEL
            └── partners/
                └── logos/
                    ├── bracongo.jpg
                    └── vodacom.png
```

**URL d'accès :**
```
https://wabracongo.ywcdigital.com/storage/partners/logos/bracongo.jpg
```

**Chemin en base de données :**
```
partners/logos/bracongo.jpg
```

**Chemin physique sur le serveur :**
```
/var/www/html/storage/app/public/partners/logos/bracongo.jpg
```

---

**Le problème des images est maintenant résolu ! 🎉**

**Prochaine étape :** Implémenter les fonctionnalités restantes 🚀
