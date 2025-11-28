# 📤 Upload des Fichiers Build vers le Serveur

## ✅ Build Compilé Localement

Les assets ont été compilés avec succès dans le dossier `public/build/` :

```
✓ public/build/.vite/manifest.json      0.33 kB │ gzip:  0.17 kB
✓ public/build/assets/app-Bz2lFR3n.css  53.89 kB │ gzip:  9.24 kB
✓ public/build/assets/app-CJy8ASEk.js   80.95 kB │ gzip: 30.35 kB
```

---

## 📂 Fichiers à Uploader

**Dossier local:**
```
C:\YESWECANGE\can-activation-kinshasa\public\build\
```

**Contenu à uploader:**
```
public/build/
├── .vite/
│   └── manifest.json
└── assets/
    ├── app-Bz2lFR3n.css
    └── app-CJy8ASEk.js
```

---

## 🚀 Méthodes d'Upload

### Méthode 1: Via FTP/SFTP (Recommandée)

**Avec FileZilla ou WinSCP:**

1. **Se connecter au serveur**
   - Host: `ton-serveur.com`
   - Port: `22` (SFTP) ou `21` (FTP)
   - Username: `ton-user`
   - Password: `ton-password`

2. **Naviguer vers le dossier `/app/public/`**

3. **Uploader le dossier `build/`**
   - Glisser-déposer le dossier `build` complet
   - Destination: `/app/public/build/`

4. **Vérifier les permissions**
   ```bash
   chmod -R 755 /app/public/build
   ```

---

### Méthode 2: Via SCP (Ligne de commande)

```bash
# Depuis ton PC local (PowerShell ou Git Bash)
scp -r C:\YESWECANGE\can-activation-kinshasa\public\build user@serveur:/app/public/

# Exemple concret
scp -r public\build deploy@wabracongo.ywcdigital.com:/app/public/
```

---

### Méthode 3: Via Git (Si configuré)

```bash
# Sur ton PC local
git add public/build/
git commit -m "Add compiled assets"
git push origin main

# Sur le serveur
cd /app
git pull origin main
```

---

### Méthode 4: Via rsync (Plus rapide pour mises à jour)

```bash
# Depuis ton PC local
rsync -avz public/build/ user@serveur:/app/public/build/

# Exemple concret
rsync -avz public/build/ deploy@wabracongo.ywcdigital.com:/app/public/build/
```

---

## ✅ Vérification Post-Upload

### 1. Vérifier que les fichiers existent sur le serveur

```bash
# Se connecter au serveur via SSH
ssh user@serveur

# Vérifier les fichiers
ls -la /app/public/build/
ls -la /app/public/build/.vite/
ls -la /app/public/build/assets/

# Tu devrais voir :
# /app/public/build/.vite/manifest.json
# /app/public/build/assets/app-Bz2lFR3n.css
# /app/public/build/assets/app-CJy8ASEk.js
```

### 2. Vérifier le contenu du manifest.json

```bash
cat /app/public/build/.vite/manifest.json
```

Résultat attendu (quelque chose comme) :
```json
{
  "resources/css/app.css": {
    "file": "assets/app-Bz2lFR3n.css",
    "isEntry": true,
    "src": "resources/css/app.css"
  },
  "resources/js/app.js": {
    "file": "assets/app-CJy8ASEk.js",
    "isEntry": true,
    "src": "resources/js/app.js"
  }
}
```

### 3. Vérifier les permissions

```bash
# Les fichiers doivent être lisibles par le serveur web
chmod -R 755 /app/public/build/
chown -R www-data:www-data /app/public/build/
```

### 4. Tester l'application

Ouvre ton navigateur :
```
https://wabracongo.ywcdigital.com/admin/login
```

**Résultat attendu :**
- ✅ Plus d'erreur "Vite manifest not found"
- ✅ La page se charge correctement
- ✅ Les styles CSS sont appliqués (Tailwind)
- ✅ Pas d'erreur dans la console navigateur

---

## 🔍 Debugging

### Si l'erreur persiste après upload

**1. Vérifier le chemin dans .env**

```bash
# Sur le serveur
cat /app/.env | grep APP_URL

# Doit être :
APP_URL=https://wabracongo.ywcdigital.com
```

**2. Vider les caches Laravel**

```bash
cd /app
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear
```

**3. Vérifier les logs Apache/Nginx**

```bash
# Apache
tail -f /var/log/apache2/error.log

# Nginx
tail -f /var/log/nginx/error.log
```

**4. Vérifier les permissions du dossier public**

```bash
ls -la /app/public/

# Doit être accessible en lecture
chmod -R 755 /app/public/
```

---

## 🎯 Alternative Rapide

Si tu n'as pas accès SSH/FTP, tu peux **recompiler sur le serveur directement** :

```bash
# Se connecter au serveur
ssh user@serveur

# Aller dans le dossier de l'app
cd /app

# Installer les dépendances Node
npm install

# Compiler les assets
npm run build

# Vérifier les fichiers générés
ls -la public/build/
```

---

## 📋 Checklist Upload

- [ ] ✅ Build compilé localement (`npm run build`)
- [ ] ✅ Dossier `public/build/` uploadé sur le serveur
- [ ] ✅ Fichier `/app/public/build/.vite/manifest.json` existe
- [ ] ✅ Fichiers CSS et JS dans `/app/public/build/assets/`
- [ ] ✅ Permissions correctes (755)
- [ ] ✅ Caches Laravel vidés
- [ ] ✅ Test dans le navigateur: aucune erreur Vite
- [ ] ✅ Styles CSS chargés correctement

---

## 🚀 Automatisation Future

Pour éviter de devoir uploader manuellement à chaque changement, configure un **pipeline CI/CD** :

### Avec GitHub Actions

Crée `.github/workflows/deploy.yml` :

```yaml
name: Deploy

on:
  push:
    branches: [ main ]

jobs:
  deploy:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v2

      - name: Setup Node
        uses: actions/setup-node@v2
        with:
          node-version: '18'

      - name: Build assets
        run: |
          npm install
          npm run build

      - name: Deploy to server
        uses: appleboy/scp-action@master
        with:
          host: ${{ secrets.HOST }}
          username: ${{ secrets.USERNAME }}
          key: ${{ secrets.SSH_KEY }}
          source: "public/build/"
          target: "/app/public/"
```

---

**Une fois uploadé, l'erreur Vite sera résolue ! ✅**
