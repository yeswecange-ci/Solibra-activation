# Guide du Flow Twilio Intelligent - CAN 2025 Solibra

## 📋 Vue d'ensemble

Ce guide explique comment le nouveau système intelligent gère l'inscription des utilisateurs WhatsApp en détectant automatiquement leur état et en reprenant là où ils se sont arrêtés.

---

## 🎯 Fonctionnalités principales

### 1. **Détection intelligente de l'état**
Lorsqu'un utilisateur envoie un message, le système vérifie automatiquement :
- ✅ Est-ce qu'il existe en base de données ?
- ✅ A-t-il répondu à la question 1 (boisson préférée) ?
- ✅ A-t-il répondu à la question 2 (quiz FIF) ?
- ✅ A-t-il accepté les politiques de confidentialité ?

### 2. **Reprise intelligente**
Le flow reprend exactement où l'utilisateur s'est arrêté :
- Si **manque boisson** → Pose question 1
- Si **manque quiz** → Pose question 2
- Si **manque politiques** → Demande acceptation
- Si **tout complété** → Affiche un résumé de ses réponses

### 3. **Affichage des réponses**
Pour un utilisateur ayant tout complété, le système affiche :
```
🎉 Tu as déjà participé !

📋 Voici tes réponses :

🥤 Boisson préférée : BOCK
⚽ Quiz FIF : OUI
✅ Politiques acceptées le : 25/12/2025 à 19:30

🍀 Résultats bientôt disponibles !
```

---

## 🔧 Endpoints API mis à jour

### 1. **POST `/api/can/check-user`**

**Requête :**
```json
{
  "phone": "+2250757123456"
}
```

**Réponses possibles :**

#### A) Utilisateur n'existe pas (`NOT_FOUND`)
```json
{
  "status": "NOT_FOUND",
  "message": "User not found"
}
```

#### B) Utilisateur incomplet (`INCOMPLETE`)
```json
{
  "status": "INCOMPLETE",
  "name": "Participant_3456",
  "phone": "+2250757123456",
  "user_id": 1,
  "has_boisson_preferee": true,
  "has_quiz_answer": false,
  "has_accepted_policies": false,
  "boisson_preferee": "BOCK",
  "quiz_answer": null,
  "message": "User exists but has not completed all questions"
}
```

#### C) Utilisateur complet (`COMPLETE`)
```json
{
  "status": "COMPLETE",
  "name": "Participant_3456",
  "phone": "+2250757123456",
  "user_id": 1,
  "boisson_preferee": "BOCK",
  "quiz_answer": "OUI",
  "accepted_policies_at": "25/12/2025 à 19:30",
  "opted_in_at": "25/12/2025 à 19:25",
  "message": "User has completed all questions",
  "completion_summary": "🎉 Tu as déjà participé !\n\n📋 Voici tes réponses :\n\n🥤 Boisson préférée : BOCK\n⚽ Quiz FIF : OUI\n✅ Politiques acceptées le : 25/12/2025 à 19:30\n\n🍀 Résultats bientôt disponibles !"
}
```

#### D) Utilisateur STOP (`STOP`)
```json
{
  "status": "STOP",
  "name": "Participant_3456",
  "phone": "+2250757123456",
  "message": "User was stopped"
}
```

### 2. **POST `/api/can/inscription-simple` (mis à jour)**

**Fonctionnement :**
- Accepte des mises à jour **partielles** ou **complètes**
- Permet de sauvegarder chaque réponse séparément
- Gère l'acceptation des politiques

**Requête complète :**
```json
{
  "phone": "+2250757123456",
  "answer_1": "BOCK",
  "answer_2": "OUI",
  "accepted_policies": true,
  "timestamp": "2025-12-25 19:30:00"
}
```

**Requêtes partielles possibles :**

```json
// Sauvegarder seulement la boisson
{
  "phone": "+2250757123456",
  "answer_1": "BOCK"
}

// Sauvegarder seulement le quiz
{
  "phone": "+2250757123456",
  "answer_2": "OUI"
}

// Sauvegarder seulement l'acceptation des politiques
{
  "phone": "+2250757123456",
  "accepted_policies": true
}
```

**Réponse :**
```json
{
  "success": true,
  "message": "User data saved successfully",
  "user_id": 1,
  "name": "Participant_3456",
  "has_boisson": true,
  "has_quiz_answer": true,
  "has_accepted_policies": true
}
```

---

## 📊 Interface Admin mise à jour

### Nouvelles colonnes dans la table Utilisateurs

1. **Quiz FIF** : Affiche OUI (vert) ou NON (rouge)
2. **Politiques** : Affiche "Acceptées" (vert) avec date ou "Non" (gris)

### Nouveaux filtres disponibles

1. **Boisson** : Filtre par boisson préférée
2. **Quiz FIF** : Filtre par réponse au quiz (OUI / NON)
3. **Village** : Filtre par village
4. **Recherche** : Par nom ou téléphone

### Exemple d'affichage

| Joueur | Téléphone | Village | Quiz FIF | Politiques | Inscrit le |
|--------|-----------|---------|----------|------------|------------|
| Participant_3456 | +2250757... | Gombe | ✓ OUI (vert) | ✓ Acceptées<br>25/12/2025 19:30 | 25/12/2025 |
| Participant_7890 | +2250701... | Plateau | ✗ NON (rouge) | ✗ Non | 24/12/2025 |

---

## 🔄 Logique du Flow Intelligent

### Diagramme de décision

```
┌─────────────────────┐
│  Message reçu       │
└──────────┬──────────┘
           │
           ▼
┌─────────────────────┐
│  check-user API     │
└──────────┬──────────┘
           │
     ┌─────┴─────┬──────────┬─────────┐
     │           │          │         │
  NOT_FOUND  INCOMPLETE  COMPLETE   STOP
     │           │          │         │
     ▼           ▼          ▼         ▼
┌─────────┐ ┌─────────┐ ┌─────────┐ ┌─────────┐
│Inscrire │ │ Reprendre│ │Afficher │ │Réactiver│
│complet  │ │là où s'  │ │réponses │ │   ?     │
│         │ │est arrêté│ │         │ │         │
└─────────┘ └─────────┘ └─────────┘ └─────────┘
```

### Gestion des cas INCOMPLETE

Quand `status = INCOMPLETE`, le flow vérifie dans l'ordre :

```javascript
if (!has_boisson_preferee) {
  → Poser question 1 (boisson)
  → Sauvegarder answer_1
}

else if (!has_quiz_answer) {
  → Poser question 2 (quiz FIF)
  → Sauvegarder answer_2
}

else if (!has_accepted_policies) {
  → Envoyer PDF + demander acceptation
  → Sauvegarder accepted_policies = true
}
```

---

## 🎬 Exemple de scénarios

### Scénario 1 : Nouvel utilisateur

**Message initial :**
```
Utilisateur: "Bonjour"
```

**Flow :**
1. `check-user` → status = `NOT_FOUND`
2. Message de bienvenue
3. Question 1 : Boisson préférée
4. Utilisateur répond : "BOCK"
5. **Sauvegarde partielle** : `{phone, answer_1: "BOCK"}`
6. Question 2 : Quiz FIF
7. Utilisateur répond : "OUI"
8. **Sauvegarde partielle** : `{phone, answer_2: "OUI"}`
9. Envoi PDF + demande acceptation
10. Utilisateur répond : "OUI"
11. **Sauvegarde finale** : `{phone, accepted_policies: true}`
12. Message de confirmation

### Scénario 2 : Utilisateur interrompu

**Contexte :**
- L'utilisateur a répondu à la question 1 (BOCK)
- Il a fermé l'appli avant la question 2

**Message de retour :**
```
Utilisateur: "Salut"
```

**Flow :**
1. `check-user` → status = `INCOMPLETE`
2. `has_boisson_preferee` = true
3. `has_quiz_answer` = false
4. `has_accepted_policies` = false
5. **Le flow saute la question 1** ✓
6. **Pose directement la question 2**
7. Continue normalement

### Scénario 3 : Utilisateur complété

**Message :**
```
Utilisateur: "Hello"
```

**Flow :**
1. `check-user` → status = `COMPLETE`
2. Affichage immédiat du résumé :
```
🎉 Tu as déjà participé !

📋 Voici tes réponses :

🥤 Boisson préférée : BOCK
⚽ Quiz FIF : OUI
✅ Politiques acceptées le : 25/12/2025 à 19:30

🍀 Résultats bientôt disponibles !
```
3. Fin du flow

---

## 🔐 Base de données

### Schéma users mis à jour

```sql
CREATE TABLE users (
    id BIGINT PRIMARY KEY,
    phone VARCHAR(255) UNIQUE,
    name VARCHAR(255),
    boisson_preferee VARCHAR(255) NULL,      -- Question 1
    quiz_answer VARCHAR(255) NULL,           -- Question 2
    accepted_policies_at TIMESTAMP NULL,     -- Date acceptation
    village_id BIGINT NULL,
    registration_status VARCHAR(255),
    opted_in_at TIMESTAMP NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

### États de complétion

| boisson | quiz | policies | État | Action |
|---------|------|----------|------|--------|
| ✗ | ✗ | ✗ | INCOMPLETE | Question 1 |
| ✓ | ✗ | ✗ | INCOMPLETE | Question 2 |
| ✓ | ✓ | ✗ | INCOMPLETE | Politiques |
| ✓ | ✓ | ✓ | COMPLETE | Résumé |

---

## 📝 Intégration Twilio Studio

### Variables du flow à utiliser

```liquid
{{widgets.http_check_user.parsed.status}}                 // NOT_FOUND, INCOMPLETE, COMPLETE, STOP
{{widgets.http_check_user.parsed.has_boisson_preferee}}   // true/false
{{widgets.http_check_user.parsed.has_quiz_answer}}        // true/false
{{widgets.http_check_user.parsed.has_accepted_policies}}  // true/false
{{widgets.http_check_user.parsed.completion_summary}}      // Message formaté
{{widgets.http_check_user.parsed.boisson_preferee}}       // Valeur
{{widgets.http_check_user.parsed.quiz_answer}}             // Valeur
```

### States recommandés

```json
{
  "check_user_status": {
    "type": "split-based-on",
    "transitions": [
      {
        "condition": "status == COMPLETE",
        "next": "send_completion_summary"
      },
      {
        "condition": "status == INCOMPLETE",
        "next": "check_what_is_missing"
      },
      {
        "condition": "status == NOT_FOUND",
        "next": "send_welcome"
      },
      {
        "condition": "status == STOP",
        "next": "ask_reactivation"
      }
    ]
  },

  "check_what_is_missing": {
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
}
```

### Appels API à faire

```json
// Après question 1
{
  "url": "http://localhost/api/can/inscription-simple",
  "body": {
    "phone": "{{flow.variables.phone_number}}",
    "answer_1": "{{flow.variables.answer_1}}"
  }
}

// Après question 2
{
  "url": "http://localhost/api/can/inscription-simple",
  "body": {
    "phone": "{{flow.variables.phone_number}}",
    "answer_2": "{{flow.variables.answer_2}}"
  }
}

// Après acceptation politiques
{
  "url": "http://localhost/api/can/inscription-simple",
  "body": {
    "phone": "{{flow.variables.phone_number}}",
    "accepted_policies": true
  }
}
```

---

## 🚀 Mise en production

### Étapes de déploiement

1. **Backend Laravel :**
   ```bash
   php artisan migrate
   ```

2. **Vérifier qu'il y a au moins un village actif :**
   ```bash
   php artisan tinker
   Village::where('is_active', true)->count();
   ```

3. **Mettre à jour l'URL dans le flow JSON :**
   - Chercher : `http://localhost`
   - Remplacer par : `https://votre-domaine.com`

4. **Importer le flow dans Twilio Studio**

5. **Tester avec plusieurs scénarios :**
   - Nouvel utilisateur (complet)
   - Utilisateur interrompu à Q1
   - Utilisateur interrompu à Q2
   - Utilisateur interrompu avant politiques
   - Utilisateur déjà complété

---

## 📊 Rapports Admin

### Requêtes utiles

```php
// Utilisateurs ayant tout complété
User::whereNotNull('boisson_preferee')
    ->whereNotNull('quiz_answer')
    ->whereNotNull('accepted_policies_at')
    ->count();

// Utilisateurs incomplets
User::where(function($q) {
    $q->whereNull('boisson_preferee')
      ->orWhereNull('quiz_answer')
      ->orWhereNull('accepted_policies_at');
})->count();

// Répartition des réponses quiz
User::whereNotNull('quiz_answer')
    ->select('quiz_answer', DB::raw('count(*) as total'))
    ->groupBy('quiz_answer')
    ->get();
```

---

## ⚠️ Points d'attention

1. **Mises à jour partielles** : L'endpoint `inscription-simple` met à jour uniquement les champs fournis
2. **Idempotence** : Appeler plusieurs fois avec les mêmes données ne pose pas de problème
3. **Validation** : La boisson doit toujours être validée par la Twilio Function
4. **Date politiques** : `accepted_policies_at` est sauvegardé automatiquement au moment de l'acceptation
5. **Status INSCRIT** : N'est assigné qu'une fois les politiques acceptées

---

## 🔧 Dépannage

### Problème : L'utilisateur voit toujours le message complet

**Cause** : `check-user` retourne `NOT_FOUND` au lieu de `COMPLETE`

**Solution** :
```bash
# Vérifier en base
php artisan tinker
User::where('phone', '+2250757123456')->first();
```

### Problème : Les réponses ne sont pas sauvegardées

**Cause** : L'endpoint `inscription-simple` ne reçoit pas les données

**Solution** :
- Vérifier les logs : `tail -f storage/logs/laravel.log`
- Vérifier le body de la requête HTTP dans Twilio Studio Debugger

### Problème : Le filtre Quiz ne fonctionne pas

**Cause** : La migration n'a pas été exécutée

**Solution** :
```bash
php artisan migrate
php artisan cache:clear
```

---

## 📚 Documentation API complète

Consultez `docs/twilio/README.md` pour la documentation détaillée des endpoints.

---

**Mis à jour le** : 25/12/2025
**Version** : 2.0 (Flow Intelligent)
