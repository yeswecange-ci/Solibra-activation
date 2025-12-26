# Flow Intelligent V3 - Avec demande de Nom/Pseudo

## 🆕 Nouveauté V3

Dans cette version, **le nom/pseudo est demandé en premier** au lieu de générer un nom automatique `Participant_XXXX`.

---

## 📊 Différence avec V2

### Flow V2 (ancien)
```
User: "Bonjour"
→ check-user → NOT_FOUND
→ Message de bienvenue
→ Question 1 (boisson)  ← Sauvegarde avec nom "Participant_3456"
→ Question 2 (quiz)
→ Politiques
```

### Flow V3 (nouveau)
```
User: "Bonjour"
→ check-user → NOT_FOUND
→ Message de bienvenue
→ "Quel est ton nom ou pseudo ?" ← NOUVEAU
→ User répond "Jean Kouassi"
→ Sauvegarde nom en BD ← NOUVEAU
→ Question 1 (boisson)
→ Question 2 (quiz)
→ Politiques
```

---

## 🔧 Modifications techniques

### 1. API Laravel - Endpoint `inscription-simple` mis à jour

**Nouveau paramètre accepté :**

```php
// app/Http/Controllers/Api/TwilioStudioController.php

public function inscriptionSimple(Request $request)
{
    $validated = $request->validate([
        'phone'     => 'required|string',
        'name'      => 'nullable|string|min:2',  // ← NOUVEAU
        'answer_1'  => 'nullable|string',
        'answer_2'  => 'nullable|string',
        'accepted_policies' => 'nullable|boolean',
        'timestamp' => 'nullable|string',
    ]);

    // ...

    // Utiliser le nom fourni ou générer un nom par défaut
    $userName = isset($validated['name'])
        ? ucwords(strtolower($validated['name']))
        : 'Participant_' . substr($phone, -4);
}
```

**Requête avec nom :**
```json
POST /api/can/inscription-simple
{
  "phone": "+2250757123456",
  "name": "Jean Kouassi"
}
```

**Réponse :**
```json
{
  "success": true,
  "user_id": 123,
  "name": "Jean Kouassi",  // ← Nom personnalisé
  "has_boisson": false,
  "has_quiz_answer": false,
  "has_accepted_policies": false
}
```

---

### 2. Nouveaux States dans le Flow Twilio

#### State : `msg_demande_nom`

**Type :** `send-and-wait-for-reply`

**Message affiché :**
```
👤 Pour commencer, quel est ton nom ou pseudo ?

(Tu peux utiliser ton vrai nom ou un surnom)
```

**Transitions :**
- `incomingMessage` → `set_user_name`
- `timeout` → `http_log_timeout`
- `deliveryFailure` → `http_log_error`

---

#### State : `set_user_name`

**Type :** `set-variables`

**Variable stockée :**
```json
{
  "key": "user_name",
  "value": "{{widgets.msg_demande_nom.inbound.Body}}"
}
```

**Transition :**
- `next` → `http_save_name`

---

#### State : `http_save_name`

**Type :** `make-http-request`

**Requête :**
```json
{
  "method": "POST",
  "url": "https://app-can-solibra.ywcdigital.com/api/can/inscription-simple",
  "body": {
    "phone": "{{flow.variables.phone_number}}",
    "name": "{{flow.variables.user_name}}",
    "timestamp": "{{flow.variables.timestamp}}"
  }
}
```

**Impact :** L'utilisateur est créé en BD avec le nom personnalisé

**Transitions :**
- `success` → `function_1` (délai puis question 1)
- `failed` → `function_1` (continue même si échec)

---

## 🎬 Scénario complet avec nom

### Conversation WhatsApp

```
User: "Bonjour"

Bot: 👋 Salut !
     ⚽🎯 Réponds à 2 questions et gagne ton PASS VIP pour 2 aux Villages Foot SOLIBRA !
     🍻 Consommation gratuite incluse !

Bot: 👤 Pour commencer, quel est ton nom ou pseudo ?
     (Tu peux utiliser ton vrai nom ou un surnom)

User: "Jean Kouassi"

[API sauvegarde: phone + name = "Jean Kouassi"]

Bot: QUESTION 1/2 🥤
     Quelle est ta boisson SOLIBRA préférée ?

User: "BOCK"

[API sauvegarde: answer_1 = "BOCK"]

Bot: QUESTION 2/2 ⚽
     BOCK et WORLD COLA sont-ils partenaires de la FIF ?
     Tape OUI ou NON

User: "OUI"

[API sauvegarde: answer_2 = "OUI"]

Bot: 📋 Pour confirmer ta participation :
     ✅ Valide la politique de confidentialité
     ✅ Confirme avoir plus de 18 ans
     ✅ Accepte les conditions générales
     👉 Tape OUI pour valider ta participation

User: "OUI"

[API sauvegarde: accepted_policies = true, status = "INSCRIT"]

Bot: 🎊 Félicitations ! 🎊
     Tu seras contacté(e) en cas de tirage victorieux ! 🍀
```

---

## 📋 En base de données

**Table `users` après inscription complète :**

| Champ | Valeur |
|-------|--------|
| `name` | **Jean Kouassi** (personnalisé) |
| `phone` | +2250757123456 |
| `boisson_preferee` | BOCK |
| `quiz_answer` | OUI |
| `accepted_policies_at` | 2025-12-26 01:09:00 |
| `registration_status` | INSCRIT |
| `source_type` | WHATSAPP_FLOW |
| `source_detail` | FlowSimpleSocialV2 |

**Avant V3 :** `name` aurait été `Participant_3456` ❌
**Avec V3 :** `name` est `Jean Kouassi` ✅

---

## 🔍 Validation du nom

Le nom est formaté automatiquement par l'API :

```php
ucwords(strtolower($validated['name']))
```

**Exemples :**

| Entrée utilisateur | Sauvegardé en BD |
|-------------------|------------------|
| `JEAN KOUASSI` | `Jean Kouassi` |
| `jean kouassi` | `Jean Kouassi` |
| `Jean KOUASSI` | `Jean Kouassi` |
| `aKouA` | `Akoua` |

**Validation :**
- Minimum : 2 caractères
- Pas de validation stricte sur les caractères (accepte accents, espaces, etc.)

---

## 🚀 Déploiement

### 1. Backend Laravel (déjà fait ✅)

Les modifications ont déjà été appliquées :
- `TwilioStudioController::inscriptionSimple()` accepte le paramètre `name`
- Formatage automatique avec `ucwords(strtolower())`

### 2. Importer le flow dans Twilio Studio

```bash
# Le fichier est ici :
docs/twilio/flow_with_name_v3_production.json
```

**Étapes :**
1. Ouvrir votre flow Twilio Studio
2. Cliquer sur **"Import from JSON"**
3. Copier le contenu de `flow_with_name_v3_production.json`
4. Coller et importer
5. **PUBLIER** le flow

### 3. Tester

**Test complet :**
```bash
php test_with_name.php
```

Ou manuellement depuis WhatsApp :
1. Envoyer "Bonjour"
2. Répondre avec votre nom (ex: "Jean Kouassi")
3. Vérifier en BD que `name` = "Jean Kouassi" (pas Participant_XXXX)

---

## 📊 Comparaison des versions

| Caractéristique | V2 | V3 |
|----------------|----|----|
| Demande de nom | ❌ | ✅ |
| Nom en BD | `Participant_XXXX` | Nom personnalisé |
| Détection d'état | ✅ | ✅ |
| Reprise intelligente | ✅ | ✅ |
| Sauvegarde incrémentale | ✅ | ✅ |
| Message de résumé | ✅ | ✅ |
| URL production | ✅ | ✅ |

---

## ⚠️ Migration depuis V2

Si vous aviez déjà des utilisateurs avec V2 :

```sql
-- Voir combien ont un nom générique
SELECT COUNT(*) FROM users
WHERE name LIKE 'Participant_%';

-- Ces utilisateurs conservent leur nom générique
-- Les nouveaux utilisateurs auront un nom personnalisé
```

**Pas de migration nécessaire** - Les deux systèmes cohabitent parfaitement.

---

## 📦 Fichiers du système V3

```
docs/twilio/
├── flow_with_name_v3_production.json    ← Nouveau flow avec demande de nom
├── FLOW_V3_WITH_NAME.md                 ← Ce fichier
├── flow_intelligent_v2_production.json  ← Ancien flow (sans nom)
├── FLOW_DIFFERENCES.md
├── FLOW_INTELLIGENT_GUIDE.md
└── BUG_FIXES.md

test_with_name.php                       ← Script de test V3
```

---

## ✅ Tests effectués

```
OK Le nom/pseudo est demandé en premier
OK L'utilisateur est créé avec le nom personnalisé (pas de Participant_XXXX)
OK Les réponses sont sauvegardées incrémentalement
OK Le message de résumé fonctionne correctement
OK La détection d'état (INCOMPLETE, COMPLETE) fonctionne
OK La reprise intelligente fonctionne
```

---

**Date de création** : 26/12/2025
**Version** : 3.0 (Flow avec Nom)
**Compatibilité** : Laravel 12 + Twilio Studio
**Fichier flow** : `flow_with_name_v3_production.json`
