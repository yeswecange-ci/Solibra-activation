# 🥤 Documentation - Fonctionnalité Boisson Préférée

## 📋 Vue d'ensemble

Cette fonctionnalité permet de collecter la boisson préférée des utilisateurs lors de l'inscription et de vérifier si cette information est renseignée avant de permettre l'accès aux fonctionnalités.

---

## 🗄️ Modifications Base de Données

### Migration créée

**Fichier** : `database/migrations/2025_12_24_000001_add_boisson_preferee_to_users_table.php`

**Colonne ajoutée** :
- `boisson_preferee` : VARCHAR, NULLABLE, ajoutée après la colonne `name`

### Exécuter la migration

```bash
php artisan migrate
```

---

## 🔧 Modifications Backend

### 1. Modèle User

**Fichier** : `app/Models/User.php`

**Champ ajouté** dans `$fillable` :
```php
'boisson_preferee'
```

### 2. Endpoint `check-user` modifié

**URL** : `POST /api/can/check-user`

**Réponse modifiée** pour utilisateur INSCRIT :
```json
{
  "status": "INSCRIT",
  "name": "Jean",
  "phone": "whatsapp:+243999999999",
  "user_id": 123,
  "has_boisson_preferee": true,
  "boisson_preferee": "Coca Cola"
}
```

**Champs ajoutés** :
- `has_boisson_preferee` : Boolean indiquant si l'utilisateur a une boisson préférée
- `boisson_preferee` : La boisson préférée (null si non renseignée)

### 3. Endpoint `inscription` modifié

**URL** : `POST /api/can/inscription`

**Nouveau paramètre optionnel** :
```json
{
  "phone": "whatsapp:+243999999999",
  "name": "Jean",
  "boisson_preferee": "Coca Cola",  // ← NOUVEAU (optionnel)
  "source_type": "AFFICHE",
  "source_detail": "GOMBE",
  "status": "INSCRIT",
  "timestamp": "2025-12-24 10:30:00"
}
```

### 4. Nouvel Endpoint `set-boisson`

**URL** : `POST /api/can/set-boisson`

**Description** : Enregistre la boisson préférée d'un utilisateur existant

**Requête** :
```json
{
  "phone": "whatsapp:+243999999999",
  "boisson_preferee": "Coca Cola"
}
```

**Réponse Success** :
```json
{
  "success": true,
  "message": "Boisson préférée enregistrée",
  "boisson_preferee": "Coca Cola"
}
```

**Réponse Error (utilisateur non trouvé)** :
```json
{
  "success": false,
  "message": "User not found"
}
```

**Code HTTP** : 404

---

## 🎯 Choix de Boissons Disponibles

**Liste recommandée** (à configurer dans Twilio Studio) :

1. **Bock** (marque Solibra)
2. **33 Export** (marque Solibra)
3. **World Cola**
4. **Coca Cola**
5. **Fanta Orange**
6. **Sprite**
7. **Eau minérale**
8. **Autre**

---

## 🔄 Flow Twilio Studio - Logique d'intégration

### Scénario 1 : Nouvelle Inscription

**Flow** :
```
1. Message bienvenue
2. Opt-in (OUI/NON)
3. Demande nom
4. Demande boisson préférée ⭐ NOUVEAU
5. Enregistrement complet (avec boisson)
6. Message confirmation
```

**États Twilio Studio à ajouter** :

#### État : `msg_demande_boisson`
```json
{
  "name": "msg_demande_boisson",
  "type": "send-and-wait-for-reply",
  "properties": {
    "body": "Merci {{flow.variables.user_name}} ! 🍹\n\nQuelle est ta boisson préférée ?\n\n1. Bock\n2. 33 Export\n3. World Cola\n4. Coca Cola\n5. Fanta Orange\n6. Sprite\n7. Eau minérale\n8. Autre\n\n👉 Tape le numéro (1-8)",
    "timeout": "3600"
  }
}
```

#### État : `validate_boisson`
```json
{
  "name": "validate_boisson",
  "type": "split-based-on",
  "transitions": [
    {
      "next": "set_boisson_bock",
      "event": "match",
      "conditions": [{
        "arguments": ["{{widgets.msg_demande_boisson.inbound.Body}}"],
        "type": "matches_any_of",
        "value": "1,BOCK,Bock,bock"
      }]
    },
    {
      "next": "set_boisson_33export",
      "event": "match",
      "conditions": [{
        "arguments": ["{{widgets.msg_demande_boisson.inbound.Body}}"],
        "type": "matches_any_of",
        "value": "2,33 EXPORT,33 Export,33export"
      }]
    },
    // ... autres options
    {
      "next": "msg_retry_boisson",
      "event": "noMatch"
    }
  ]
}
```

#### État : `set_boisson_*` (exemple pour Bock)
```json
{
  "name": "set_boisson_bock",
  "type": "set-variables",
  "properties": {
    "variables": [{
      "value": "Bock",
      "key": "boisson_preferee"
    }]
  },
  "transitions": [{
    "next": "http_log_inscription",
    "event": "next"
  }]
}
```

#### Modification de `http_log_inscription`
Ajouter la boisson au body :
```json
{
  "body": "{\n  \"phone\": \"{{flow.variables.phone_number}}\",\n  \"name\": \"{{flow.variables.user_name}}\",\n  \"boisson_preferee\": \"{{flow.variables.boisson_preferee}}\",\n  \"source_type\": \"{{flow.variables.source_type}}\",\n  \"source_detail\": \"{{flow.variables.source_detail}}\",\n  \"status\": \"INSCRIT\",\n  \"timestamp\": \"{{now | date: '%Y-%m-%d %H:%M:%S'}}\"\n}"
}
```

---

### Scénario 2 : Utilisateur Existant Sans Boisson

**Flow** :
```
1. User envoie un message
2. API check-user → has_boisson_preferee = false
3. Message : "Avant de continuer, quelle est ta boisson préférée ?"
4. Demande boisson préférée
5. API set-boisson
6. Continuer le flow normal
```

**États Twilio Studio à ajouter** :

#### Modification de `check_user_status`
Ajouter une condition pour vérifier `has_boisson_preferee` :

```json
{
  "name": "check_user_status",
  "type": "split-based-on",
  "transitions": [
    {
      "next": "http_log_scan",
      "event": "match",
      "conditions": [{
        "friendly_name": "Nouvel utilisateur",
        "arguments": ["{{widgets.http_check_user.parsed.status}}"],
        "type": "equal_to",
        "value": "NOT_FOUND"
      }]
    },
    {
      "next": "check_has_boisson",  // ← NOUVEAU
      "event": "match",
      "conditions": [{
        "friendly_name": "Déjà inscrit",
        "arguments": ["{{widgets.http_check_user.parsed.status}}"],
        "type": "equal_to",
        "value": "INSCRIT"
      }]
    }
  ]
}
```

#### État : `check_has_boisson`
```json
{
  "name": "check_has_boisson",
  "type": "split-based-on",
  "transitions": [
    {
      "next": "msg_demande_boisson_manquante",
      "event": "match",
      "conditions": [{
        "friendly_name": "Pas de boisson",
        "arguments": ["{{widgets.http_check_user.parsed.has_boisson_preferee}}"],
        "type": "equal_to",
        "value": "false"
      }]
    },
    {
      "next": "http_check_pronostics",  // Flow normal
      "event": "match",
      "conditions": [{
        "friendly_name": "A une boisson",
        "arguments": ["{{widgets.http_check_user.parsed.has_boisson_preferee}}"],
        "type": "equal_to",
        "value": "true"
      }]
    }
  ]
}
```

#### État : `msg_demande_boisson_manquante`
```json
{
  "name": "msg_demande_boisson_manquante",
  "type": "send-and-wait-for-reply",
  "properties": {
    "body": "👋 Salut {{widgets.http_check_user.parsed.name}} !\n\nAvant de continuer, j'ai besoin d'une info :\n\n🍹 Quelle est ta boisson préférée ?\n\n1. Bock\n2. 33 Export\n3. World Cola\n4. Coca Cola\n5. Fanta Orange\n6. Sprite\n7. Eau minérale\n8. Autre\n\n👉 Tape le numéro (1-8)",
    "timeout": "3600"
  }
}
```

#### État : `http_save_boisson_existant`
```json
{
  "name": "http_save_boisson_existant",
  "type": "make-http-request",
  "properties": {
    "method": "POST",
    "url": "https://can-wabracongo.ywcdigital.com/api/can/set-boisson",
    "content_type": "application/json",
    "body": "{\n  \"phone\": \"{{flow.variables.phone_number}}\",\n  \"boisson_preferee\": \"{{flow.variables.boisson_preferee}}\"\n}"
  },
  "transitions": [
    {
      "next": "msg_boisson_enregistree",
      "event": "success"
    },
    {
      "next": "http_check_pronostics",  // Continuer même si erreur
      "event": "failed"
    }
  ]
}
```

#### État : `msg_boisson_enregistree`
```json
{
  "name": "msg_boisson_enregistree",
  "type": "send-message",
  "properties": {
    "body": "✅ Merci ! Ta préférence pour {{flow.variables.boisson_preferee}} a été enregistrée ! 🍹"
  },
  "transitions": [{
    "next": "http_check_pronostics",
    "event": "sent"
  }]
}
```

---

## 📊 Données collectées

### Exemple de données en base

```sql
SELECT
  name,
  boisson_preferee,
  COUNT(*) as count
FROM users
WHERE boisson_preferee IS NOT NULL
GROUP BY boisson_preferee
ORDER BY count DESC;
```

**Résultat exemple** :
```
| name    | boisson_preferee | count |
|---------|------------------|-------|
| Bock    | Bock             | 450   |
| Coca    | Coca Cola        | 320   |
| 33      | 33 Export        | 280   |
| World   | World Cola       | 150   |
| Fanta   | Fanta Orange     | 120   |
```

---

## 🧪 Tests

### Test 1 : Nouvelle inscription avec boisson

**Postman Request** :
```
POST https://can-wabracongo.ywcdigital.com/api/can/inscription

Body:
{
  "phone": "whatsapp:+243999999999",
  "name": "TestUser",
  "boisson_preferee": "Bock",
  "source_type": "DIRECT",
  "source_detail": "SANS_QR",
  "status": "INSCRIT"
}
```

**Vérification** :
```sql
SELECT name, boisson_preferee FROM users WHERE phone = 'whatsapp:+243999999999';
-- Résultat attendu : TestUser | Bock
```

### Test 2 : Enregistrer boisson pour utilisateur existant

**Postman Request** :
```
POST https://can-wabracongo.ywcdigital.com/api/can/set-boisson

Body:
{
  "phone": "whatsapp:+243999999999",
  "boisson_preferee": "Coca Cola"
}
```

**Réponse attendue** :
```json
{
  "success": true,
  "message": "Boisson préférée enregistrée",
  "boisson_preferee": "Coca Cola"
}
```

### Test 3 : Check-user avec boisson

**Postman Request** :
```
POST https://can-wabracongo.ywcdigital.com/api/can/check-user

Body:
{
  "phone": "whatsapp:+243999999999"
}
```

**Réponse attendue** :
```json
{
  "status": "INSCRIT",
  "name": "TestUser",
  "phone": "whatsapp:+243999999999",
  "user_id": 1,
  "has_boisson_preferee": true,
  "boisson_preferee": "Coca Cola"
}
```

---

## 🎨 Messages WhatsApp Recommandés

### Message inscription avec boisson
```
✅ C'est bon {{user_name}} !

Tu fais partie de la TEAM SOLIBRA BABIFOOT CITY 2025 ⚽

📊 Ta boisson préférée : {{boisson_preferee}} 🍹

🎁 A gagner :
→ Casier de Bock ou World Cola
→ Bons d'achats
→ Maillots & accessoires

Prépare-toi à jouer et à gagner !

#BabiFootCity
```

### Message boisson enregistrée (utilisateur existant)
```
✅ Parfait !

Ta préférence pour {{boisson_preferee}} a été enregistrée ! 🍹

Tu peux maintenant continuer à profiter de tous les jeux et cadeaux !

#BabiFootCity
```

---

## 📈 Utilisation Marketing

### Segmentation possible

- Campagnes ciblées par boisson préférée
- Offres personnalisées selon les préférences
- Analyse des tendances de consommation
- Adaptation des prix/cadeaux aux marques populaires

### Exemple de requête pour campagne ciblée

```sql
-- Tous les users qui préfèrent Bock
SELECT phone, name
FROM users
WHERE boisson_preferee = 'Bock'
  AND is_active = TRUE;
```

---

## ✅ Checklist d'implémentation

- [x] Migration base de données créée
- [x] Modèle User mis à jour
- [x] Endpoint check-user modifié
- [x] Endpoint inscription modifié
- [x] Endpoint set-boisson créé
- [x] Route API ajoutée
- [ ] Exécuter la migration
- [ ] Modifier le flow Twilio Studio
- [ ] Tester en développement
- [ ] Tester en production
- [ ] Créer dashboard analytics boissons

---

**Date de création** : 2025-12-24
**Version** : 1.0
**Auteur** : Claude Code
