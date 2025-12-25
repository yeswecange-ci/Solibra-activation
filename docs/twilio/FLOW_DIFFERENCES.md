# Différences entre l'ancien flow et le flow intelligent V2

## 🆕 Nouveautés du Flow Intelligent V2

### 1. **Nouveau State : `check_what_is_missing`**

**Emplacement** : Après `check_user_status` quand status = `INCOMPLETE`

**Fonction** : Détermine exactement ce qui manque et saute les questions déjà répondues

```json
{
  "name": "check_what_is_missing",
  "type": "split-based-on",
  "transitions": [
    {
      "condition": "has_boisson_preferee == false",
      "next": "msg_question_1"
    },
    {
      "condition": "has_quiz_answer == false",
      "next": "msg_question_2"
    },
    {
      "condition": "has_accepted_policies == false",
      "next": "msg_pdf_validation"
    }
  ]
}
```

**Impact** : L'utilisateur ne voit plus les questions auxquelles il a déjà répondu

---

### 2. **Nouveau State : `msg_already_complete`**

**Emplacement** : Après `check_user_status` quand status = `COMPLETE`

**Fonction** : Affiche le résumé complet des réponses de l'utilisateur

```json
{
  "name": "msg_already_complete",
  "type": "send-message",
  "body": "{{widgets.http_check_user.parsed.completion_summary}}"
}
```

**Message affiché** :
```
🎉 Tu as déjà participé !

📋 Voici tes réponses :

🥤 Boisson préférée : BOCK
⚽ Quiz FIF : OUI
✅ Politiques acceptées le : 25/12/2025 à 19:30

🍀 Résultats bientôt disponibles !
```

---

### 3. **Nouveaux States API : Appels partiels**

#### **`http_save_answer_1`**
**Emplacement** : Après validation de la boisson

**Requête** :
```json
POST /api/can/inscription-simple
{
  "phone": "{{phone}}",
  "answer_1": "{{answer_1}}"
}
```

**Impact** : La boisson est sauvegardée immédiatement, même si l'utilisateur abandonne après

---

#### **`http_save_answer_2`**
**Emplacement** : Après réponse au quiz

**Requête** :
```json
POST /api/can/inscription-simple
{
  "phone": "{{phone}}",
  "answer_2": "{{answer_2}}"
}
```

**Impact** : La réponse quiz est sauvegardée immédiatement

---

#### **`http_save_policies`**
**Emplacement** : Après acceptation des politiques

**Requête** :
```json
POST /api/can/inscription-simple
{
  "phone": "{{phone}}",
  "accepted_policies": true
}
```

**Impact** : Date d'acceptation enregistrée, status → `INSCRIT`

---

### 4. **Condition INCOMPLETE ajoutée dans `check_user_status`**

**Ancien flow** :
```json
{
  "transitions": [
    {"condition": "status == NOT_FOUND", "next": "send_message_1"},
    {"condition": "status == INSCRIT", "next": "msg_deja_inscrit"},
    {"condition": "status == STOP", "next": "msg_reactivation"}
  ]
}
```

**Nouveau flow** :
```json
{
  "transitions": [
    {"condition": "status == NOT_FOUND", "next": "send_message_1"},
    {"condition": "status == COMPLETE", "next": "msg_already_complete"},
    {"condition": "status == INCOMPLETE", "next": "check_what_is_missing"},
    {"condition": "status == STOP", "next": "msg_reactivation"}
  ]
}
```

**Impact** : Gestion intelligente des utilisateurs incomplets

---

## 📊 Comparaison des flux

### Ancien Flow (V1)

```
Message → check-user → NOT_FOUND/INSCRIT/STOP
                         ↓         ↓        ↓
                       Q1→Q2→PDF  Message  Réactiver?
                                  final
```

**Problème** : Si utilisateur s'arrête à Q1, il doit tout recommencer

---

### Nouveau Flow (V2)

```
Message → check-user → NOT_FOUND/COMPLETE/INCOMPLETE/STOP
                         ↓         ↓          ↓          ↓
                       Q1→Q2→PDF  Résumé   Reprendre  Réactiver?
                                          là où
                                          arrêté
```

**Avantage** : L'utilisateur reprend exactement où il s'est arrêté

---

## 🔄 Scénarios de reprise

### Scénario A : Arrêt après Q1

**Ancien flow** :
1. Utilisateur répond "BOCK" à Q1
2. Ferme l'appli
3. **Revient** → Reçoit Q1 à nouveau ❌
4. Doit re-taper "BOCK"

**Nouveau flow** :
1. Utilisateur répond "BOCK" à Q1
2. **API save** immédiatement
3. Ferme l'appli
4. **Revient** → `check_what_is_missing` détecte `has_boisson = true`
5. **Saute Q1** ✅ → Pose directement Q2

---

### Scénario B : Arrêt après Q2

**Ancien flow** :
1. Utilisateur répond Q1 + Q2
2. Ferme avant d'accepter les politiques
3. **Revient** → Recommence à Q1 ❌

**Nouveau flow** :
1. Utilisateur répond Q1 (sauvegarde immédiate)
2. Répond Q2 (sauvegarde immédiate)
3. Ferme avant politiques
4. **Revient** → `check_what_is_missing` détecte :
   - `has_boisson = true` ✅
   - `has_quiz_answer = true` ✅
   - `has_accepted_policies = false` ❌
5. **Va directement à** `msg_pdf_validation` ✅

---

### Scénario C : Utilisateur ayant tout complété

**Ancien flow** :
1. Utilisateur complète tout
2. **Revient** → "Tu as déjà participé" (message générique)

**Nouveau flow** :
1. Utilisateur complète tout
2. **Revient** → `status = COMPLETE`
3. Affiche résumé détaillé avec :
   - Sa boisson préférée
   - Sa réponse au quiz
   - Date d'acceptation des politiques
4. **Aucune question posée** ✅

---

## 🔧 Changements techniques

### API Endpoint modifié

**Ancien** : `/api/can/inscription`
```json
// Requiert TOUS les champs en une seule fois
{
  "phone": "...",
  "name": "...",
  "boisson_preferee": "...",
  "source_type": "...",
  "source_detail": "..."
}
```

**Nouveau** : `/api/can/inscription-simple`
```json
// Accepte des mises à jour partielles
{
  "phone": "...",
  "answer_1": "..."  // OU
  "answer_2": "..."  // OU
  "accepted_policies": true
}
```

---

### Variables Twilio Studio ajoutées

**Variables de détection d'état** :
```liquid
{{widgets.http_check_user.parsed.status}}                 // COMPLETE, INCOMPLETE, NOT_FOUND, STOP
{{widgets.http_check_user.parsed.has_boisson_preferee}}   // true/false
{{widgets.http_check_user.parsed.has_quiz_answer}}        // true/false
{{widgets.http_check_user.parsed.has_accepted_policies}}  // true/false
{{widgets.http_check_user.parsed.completion_summary}}      // Message formaté
```

**Utilisation dans les conditions** :
```json
{
  "condition": "{{widgets.http_check_user.parsed.has_boisson_preferee}} == false",
  "next": "msg_question_1"
}
```

---

## 📈 Avantages mesurables

### 1. **Taux de complétion amélioré**
- **Avant** : Utilisateur abandonne à Q1 → Perd tout → Ne revient pas
- **Après** : Utilisateur abandonne à Q1 → Données sauvegardées → Reprend facilement

### 2. **Meilleure expérience utilisateur**
- Pas de questions redondantes
- Affichage de leurs réponses précédentes
- Message personnalisé pour les utilisateurs complets

### 3. **Données plus fiables**
- Chaque réponse sauvegardée immédiatement
- Moins de risque de perte de données
- Traçabilité complète avec dates

### 4. **Reporting amélioré**
- Admin peut voir qui a répondu à quoi
- Filtrage par réponse au quiz
- Filtrage par acceptation des politiques

---

## 🎯 Points d'attention pour la migration

### 1. **Remplacer l'URL de base**

Dans le nouveau JSON, chercher/remplacer :
```
http://localhost → https://votre-domaine.com
```

### 2. **Vérifier les Twilio Functions**

Les functions utilisées sont les mêmes :
- `validate_solibra_drink` (validation boisson)
- `delay_2` (délai entre messages)

### 3. **Tester tous les scénarios**

- ✅ Nouvel utilisateur (flux complet)
- ✅ Utilisateur interrompu à Q1
- ✅ Utilisateur interrompu à Q2
- ✅ Utilisateur interrompu avant politiques
- ✅ Utilisateur déjà complet qui revient
- ✅ Utilisateur STOP qui veut réactiver

### 4. **Migration des utilisateurs existants**

Si vous avez déjà des utilisateurs avec l'ancien système :
```sql
-- Vérifier combien ont une boisson mais pas le reste
SELECT COUNT(*) FROM users
WHERE boisson_preferee IS NOT NULL
AND (quiz_answer IS NULL OR accepted_policies_at IS NULL);

-- Ces utilisateurs bénéficieront de la reprise intelligente
```

---

## 📦 Fichiers du nouveau système

```
docs/twilio/
├── flow_intelligent_v2.json          ← Nouveau flow JSON
├── FLOW_INTELLIGENT_GUIDE.md         ← Guide complet
├── FLOW_DIFFERENCES.md                ← Ce fichier
└── README.md                          ← Documentation API
```

---

## 🚀 Procédure de déploiement

1. **Backend Laravel** (déjà fait ✅)
   ```bash
   php artisan migrate
   ```

2. **Twilio Studio**
   - Ouvrir le flow existant
   - Import from JSON
   - Coller le contenu de `flow_intelligent_v2.json`
   - Remplacer `http://localhost` par votre domaine
   - Publier

3. **Tests**
   - Créer un utilisateur test
   - L'arrêter à Q1
   - Revenir → Vérifier qu'il saute à Q2
   - Recommencer avec arrêt à Q2
   - etc.

4. **Monitoring**
   - Vérifier les logs Laravel : `tail -f storage/logs/laravel.log`
   - Vérifier Twilio Debugger
   - Suivre les métriques de complétion

---

**Date de mise à jour** : 25/12/2025
**Version** : 2.0 (Flow Intelligent)
**Compatibilité** : Compatible avec les utilisateurs de l'ancien flow
