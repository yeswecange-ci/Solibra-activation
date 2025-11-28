# 🧪 Guide de Test avec Postman - CAN 2025 Kinshasa

## 📥 Importer la Collection Postman

### Méthode 1: Import depuis fichier

1. Ouvre **Postman**
2. Clique sur **Import** (en haut à gauche)
3. Sélectionne le fichier `CAN_2025_Postman_Collection.json`
4. Clique sur **Import**

### Méthode 2: Copier/Coller

1. Ouvre le fichier `CAN_2025_Postman_Collection.json`
2. Copie tout le contenu
3. Dans Postman → **Import** → **Raw text**
4. Colle le JSON
5. Clique sur **Import**

---

## 🔧 Configuration de la Variable

La collection utilise une variable `{{base_url}}` qui pointe vers ton serveur.

**Par défaut:** `https://wabracongo.ywcdigital.com`

### Pour modifier l'URL:

1. Dans Postman, clique sur la collection **CAN 2025 Kinshasa - API**
2. Onglet **Variables**
3. Change `base_url` selon ton environnement:
   - **Production:** `https://wabracongo.ywcdigital.com`
   - **Local:** `http://localhost:8000`
   - **Autre:** `http://ton-serveur.com`

---

## 📋 Liste des Endpoints Testables

### 🎯 Twilio Studio Flow (8 endpoints)

| # | Endpoint | Méthode | Description |
|---|----------|---------|-------------|
| 1 | `/api/can/scan` | POST | Log du scan QR code initial |
| 2 | `/api/can/optin` | POST | Log de l'opt-in (réponse OUI) |
| 3 | `/api/can/inscription` | POST | Inscription finale avec nom |
| 4 | `/api/can/refus` | POST | Refus de l'opt-in (NON) |
| 5 | `/api/can/stop` | POST | Désinscription (STOP) |
| 6 | `/api/can/abandon` | POST | Abandon du processus |
| 7 | `/api/can/timeout` | POST | Timeout dans le flow |
| 8 | `/api/can/error` | POST | Erreur de livraison |

### 💬 WhatsApp Webhooks (2 endpoints)

| # | Endpoint | Méthode | Description |
|---|----------|---------|-------------|
| 1 | `/api/webhook/whatsapp` | POST | Recevoir messages WhatsApp |
| 2 | `/api/webhook/whatsapp/status` | POST | Statut de livraison des messages |

### 📱 QR Code Public (1 endpoint)

| # | Endpoint | Méthode | Description |
|---|----------|---------|-------------|
| 1 | `/qr/{code}` | GET | Scanner QR code (redirige vers WhatsApp) |

---

## 🧪 Scénarios de Test

### ✅ Scénario 1: Flow Complet d'Inscription

**Objectif:** Simuler l'inscription complète d'un utilisateur via Twilio Studio

**Étapes:**

1. **Scan QR Code**
   - Requête: `POST /api/can/scan`
   - Body:
   ```json
   {
     "phone": "whatsapp:+243812345678",
     "source_type": "AFFICHE",
     "source_detail": "GOMBE",
     "timestamp": "2025-11-28 12:00:00",
     "status": "SCAN"
   }
   ```
   - **Résultat attendu:** `200 OK` avec `session_id`

2. **Opt-in (OUI)**
   - Requête: `POST /api/can/optin`
   - Body:
   ```json
   {
     "phone": "whatsapp:+243812345678",
     "status": "OPT_IN",
     "timestamp": "2025-11-28 12:01:00"
   }
   ```
   - **Résultat attendu:** `200 OK` avec message de succès

3. **Inscription finale**
   - Requête: `POST /api/can/inscription`
   - Body:
   ```json
   {
     "phone": "whatsapp:+243812345678",
     "name": "Jean Kabongo",
     "source_type": "AFFICHE",
     "source_detail": "GOMBE",
     "status": "INSCRIT",
     "timestamp": "2025-11-28 12:02:00"
   }
   ```
   - **Résultat attendu:** `200 OK` avec `user_id` et `name`

**Vérifications:**
- ✅ Dans la BDD, vérifier que l'utilisateur existe dans `users`
- ✅ Vérifier `source_type = 'AFFICHE'` et `source_detail = 'GOMBE'`
- ✅ Vérifier `registration_status = 'INSCRIT'`

---

### ✅ Scénario 2: Refus d'Opt-in

**Objectif:** Simuler un utilisateur qui refuse l'opt-in

**Étapes:**

1. **Scan QR Code** (même que scénario 1)

2. **Refus Opt-in**
   - Requête: `POST /api/can/refus`
   - Body:
   ```json
   {
     "phone": "whatsapp:+243999999999",
     "status": "REFUS",
     "timestamp": "2025-11-28 12:01:30"
   }
   ```
   - **Résultat attendu:** `200 OK`

**Vérifications:**
- ✅ Dans `conversation_sessions`, vérifier `state = 'REFUS'`
- ✅ Aucun utilisateur créé dans `users`

---

### ✅ Scénario 3: Abandon du Processus

**Objectif:** Simuler un utilisateur qui abandonne en cours d'inscription

**Étapes:**

1. **Scan QR Code**
2. **Opt-in**
3. **Abandon**
   - Requête: `POST /api/can/abandon`
   - Body:
   ```json
   {
     "phone": "whatsapp:+243888888888",
     "status": "ABANDON_OPTIN",
     "timestamp": "2025-11-28 12:01:45"
   }
   ```
   - **Résultat attendu:** `200 OK`

---

### ✅ Scénario 4: Désinscription (STOP)

**Objectif:** Simuler un utilisateur qui se désinscrit

**Prérequis:** Utilisateur déjà inscrit (scénario 1)

**Étapes:**

1. **Désinscription**
   - Requête: `POST /api/can/stop`
   - Body:
   ```json
   {
     "phone": "whatsapp:+243812345678",
     "status": "STOP",
     "timestamp": "2025-11-28 12:03:00"
   }
   ```
   - **Résultat attendu:** `200 OK`

**Vérifications:**
- ✅ Dans `users`, vérifier `is_active = false`
- ✅ Vérifier `registration_status = 'STOP'`

---

### ✅ Scénario 5: Test Webhook WhatsApp

**Objectif:** Simuler un message WhatsApp entrant

**Étapes:**

1. **Recevoir Message**
   - Requête: `POST /api/webhook/whatsapp`
   - Headers: `Content-Type: application/x-www-form-urlencoded`
   - Body (x-www-form-urlencoded):
     - `From`: `whatsapp:+243812345678`
     - `Body`: `MENU`
     - `MessageSid`: `SM1234567890abcdef`
   - **Résultat attendu:** `200 OK`

**Vérifications:**
- ✅ Vérifier les logs Laravel: `tail -f storage/logs/laravel.log`
- ✅ Le bot devrait traiter le message

---

### ✅ Scénario 6: Test QR Code Public

**Objectif:** Tester le scan d'un QR code public

**Étapes:**

1. **Scanner QR Code**
   - Requête: `GET /qr/START_AFF_GOMBE`
   - **Résultat attendu:** Redirection `302` vers WhatsApp

**Codes QR disponibles:**
- `START_AFF_GOMBE` → Affiche GOMBE
- `START_AFF_MASINA` → Affiche MASINA
- `START_PDV_BRACONGO` → PDV Bracongo
- `START_FB` → Facebook
- `START_IG` → Instagram

---

## 🎯 Tests Avancés

### Test avec Sources Différentes

**Affiche:**
```json
{
  "source_type": "AFFICHE",
  "source_detail": "MASINA"
}
```

**Point de Vente:**
```json
{
  "source_type": "PDV_PARTENAIRE",
  "source_detail": "VODACOM"
}
```

**Digital:**
```json
{
  "source_type": "DIGITAL",
  "source_detail": "FB"
}
```

**Flyer:**
```json
{
  "source_type": "FLYER",
  "source_detail": "UNI"
}
```

**Direct (sans QR):**
```json
{
  "source_type": "DIRECT",
  "source_detail": "SANS_QR"
}
```

---

## 📊 Vérifications Post-Test

### 1. Vérifier dans la Base de Données

```sql
-- Voir les utilisateurs créés
SELECT * FROM users ORDER BY created_at DESC LIMIT 10;

-- Voir les sessions de conversation
SELECT * FROM conversation_sessions ORDER BY last_activity DESC LIMIT 10;

-- Statistiques par source
SELECT source_type, source_detail, COUNT(*) as total
FROM users
GROUP BY source_type, source_detail
ORDER BY total DESC;
```

### 2. Vérifier les Logs Laravel

```bash
# Sur le serveur
tail -f /app/storage/logs/laravel.log

# Rechercher les logs Twilio Studio
grep "Twilio Studio" /app/storage/logs/laravel.log

# Compter les inscriptions
grep "New user registered" /app/storage/logs/laravel.log | wc -l
```

### 3. Vérifier le Dashboard Admin

Accéder à: `https://wabracongo.ywcdigital.com/admin/dashboard`

Vérifier que les stats se mettent à jour en temps réel.

---

## ⚠️ Troubleshooting

### Erreur 500: Internal Server Error

**Causes possibles:**
- Problème de migration (vérifier que toutes les migrations sont exécutées)
- Erreur dans les logs Laravel
- Village actif manquant

**Solution:**
```bash
# Vérifier les logs
tail -f /app/storage/logs/laravel.log

# Vérifier les migrations
php artisan migrate:status

# Créer un village actif
php artisan tinker
>>> \App\Models\Village::create(['name' => 'GOMBE', 'is_active' => true]);
```

---

### Erreur 404: Not Found

**Causes possibles:**
- URL incorrecte
- Routes non enregistrées

**Solution:**
```bash
# Lister toutes les routes
php artisan route:list --path=api

# Vider le cache des routes
php artisan route:clear
```

---

### Erreur: "No active village available"

**Cause:** Aucun village actif en base de données

**Solution:**
```bash
# Via admin
https://wabracongo.ywcdigital.com/admin/villages/create

# Ou via tinker
php artisan tinker
>>> \App\Models\Village::create(['name' => 'GOMBE', 'is_active' => true]);
```

---

## 📈 Tests de Performance

### Test de charge (optionnel)

Pour tester avec plusieurs requêtes simultanées, utilise **Postman Runner**:

1. Sélectionne la collection **CAN 2025 Kinshasa - API**
2. Clique sur **Run**
3. Sélectionne les endpoints à tester
4. Configure:
   - Iterations: `10`
   - Delay: `100ms`
5. Clique sur **Run CAN 2025 Kinshasa - API**

---

## ✅ Checklist de Test Complet

- [ ] ✅ Test 1: Scan QR Code
- [ ] ✅ Test 2: Opt-in
- [ ] ✅ Test 3: Inscription complète
- [ ] ✅ Test 4: Refus opt-in
- [ ] ✅ Test 5: Stop (désinscription)
- [ ] ✅ Test 6: Abandon
- [ ] ✅ Test 7: Timeout
- [ ] ✅ Test 8: Erreur de livraison
- [ ] ✅ Test 9: Webhook WhatsApp
- [ ] ✅ Test 10: Status callback
- [ ] ✅ Test 11: Scan QR code public
- [ ] ✅ Vérifier BDD après chaque test
- [ ] ✅ Vérifier logs Laravel
- [ ] ✅ Vérifier dashboard admin

---

## 🎉 Résultat Attendu

Après tous les tests, tu devrais voir dans le dashboard admin :
- **Total Inscrits** : nombre d'utilisateurs créés
- **Villages CAN** : villages actifs
- **Pronostics** : 0 (pour le moment)
- **Messages** : 0 (si WhatsApp non configuré)

Et dans la BDD :
- Table `users` : utilisateurs créés
- Table `conversation_sessions` : sessions avec différents états
- Logs Laravel : tous les événements enregistrés

---

**Bons tests ! 🚀**
