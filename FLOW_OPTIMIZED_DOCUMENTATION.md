# 📘 Documentation - Flow Twilio Studio Optimisé

## 🎯 Vue d'ensemble

Ce flow optimisé implémente une **architecture intelligente de routage** avec :
- ✅ **Gestion robuste des erreurs** avec retry logic
- ✅ **Routage basé sur le statut d'inscription**
- ✅ **Validation stricte des entrées utilisateur**
- ✅ **Messages d'erreur contextuels et clairs**
- ✅ **Limitation des tentatives** pour éviter les boucles infinies

---

## 🏗️ Architecture du Flow

### 🔄 Flux Principal

```
Trigger
  ↓
Normalisation du message (suppression espaces, majuscules)
  ↓
Vérification STOP globale
  ↓
Vérification statut utilisateur (API)
  ↓
  ├─→ NOT_FOUND → Flow d'inscription obligatoire
  ├─→ INSCRIT → Routage intelligent vers subflows
  └─→ STOP → Proposition de réactivation
```

---

## 🎭 Cas d'utilisation détaillés

### 1️⃣ **Utilisateur NON INSCRIT (NOT_FOUND)**

**Comportement** : Peu importe le mot-clé envoyé, l'utilisateur DOIT d'abord s'inscrire.

**Flow d'inscription** :
```
1. Détection de la source (START_AFF_*, START_PDV_*, etc. ou DIRECT)
2. Log scan dans l'API
3. Message de bienvenue avec opt-in
   ├─→ OUI : Continue
   ├─→ NON : Fin avec message de refus
   ├─→ STOP : Fin avec désabonnement
   └─→ Invalide : Relance (max 1 fois) → Abandon
4. Demande du nom (min 2 caractères)
   ├─→ Nom valide : Continue
   ├─→ STOP : Désabonnement
   └─→ Invalide : Relance (max 1 fois) → Abandon
5. Enregistrement complet dans l'API
6. Message de confirmation
7. Affichage du menu principal
```

**Gestion des erreurs** :
- ❌ Réponse invalide à l'opt-in → 1 relance avec message explicatif
- ❌ Nom trop court (< 2 caractères) → 1 relance avec exemple
- ❌ 2ème réponse invalide → Abandon avec message "trop de tentatives"
- ❌ Timeout → Enregistrement et message "temps écoulé"
- ❌ Erreur API → Message "erreur technique" avec log

---

### 2️⃣ **Utilisateur INSCRIT**

**Comportement** : Routage intelligent vers les subflows selon le mot-clé.

**Mots-clés supportés** :

| Mot-clé | Variantes acceptées | Subflow |
|---------|---------------------|---------|
| **MENU** | MENU, AIDE, HELP, INFO | Menu principal |
| **MATCHS** | MATCHS, MATCH, 1 | Liste des matchs |
| **PRONOSTIC** | PRONOSTIC, PRONO, 2 | Faire un pronostic |
| **MES PRONOS** | MESPRONOS, MESPRONO, MESPRONOSTICS, 3 | Voir mes pronostics |
| **CLASSEMENT** | CLASSEMENT, RANKING, LEADERBOARD, 4 | Voir le classement |
| **QUIZ** | QUIZ, 5, SOLIBRAVILLAGECAN | Quiz sur les marques |
| **STOP** | STOP, ARRET, UNSUBSCRIBE | Désabonnement |

**Flow pour utilisateur inscrit** :
```
1. Message de bienvenue personnalisé (si pas de mot-clé reconnu)
2. Affichage du menu principal
3. Attente du choix utilisateur
4. Validation du choix
   ├─→ Choix valide (1-5) : Lancement du subflow
   ├─→ STOP : Désabonnement
   └─→ Invalide : Message d'erreur + réaffichage du menu
5. Exécution du subflow
   ├─→ Success : Fin
   └─→ Erreur : Message + retour au menu
```

**Gestion des erreurs** :
- ❌ Mot-clé non reconnu → Message de bienvenue + menu
- ❌ Choix de menu invalide → Message explicatif + réaffichage du menu
- ❌ Erreur dans un subflow → Message d'erreur + retour au menu
- ❌ Timeout au menu → Fin silencieuse (OK car déjà inscrit)

---

### 3️⃣ **Utilisateur STOP (Réactivation)**

**Comportement** : Proposition de réinscription.

**Flow de réactivation** :
```
1. Message personnalisé avec nom récupéré de l'API
2. Proposition de réactivation (OUI/NON)
3. Validation de la réponse
   ├─→ OUI : Réactivation dans l'API → Message succès → Menu
   ├─→ NON ou STOP : Message "reste désinscrit"
   └─→ Invalide : Message d'erreur + redemande (1 seule relance implicite)
4. Si réactivé : Accès au menu principal
```

**Gestion des erreurs** :
- ❌ Réponse invalide → Message "Je n'ai pas compris" + redemande
- ❌ Erreur API réactivation → Message "erreur technique"
- ❌ Timeout → Message "temps écoulé"

---

## 🔧 Améliorations Techniques

### 1. **Normalisation robuste des messages**

```json
{
  "user_message": "{{trigger.message.Body}}",
  "user_message_normalized": "{{trigger.message.Body | upcase | strip}}",
  "clean_message": "{{user_message_normalized | replace: ' ', '' | replace: '\n', ''}}"
}
```

**Avantages** :
- Gère les espaces, majuscules/minuscules, sauts de ligne
- "  ProNosTic  " → "PRONOSTIC"
- "m e s  p r o n o s" → "MESPRONOS"

---

### 2. **Système de retry avec compteur**

```json
{
  "retry_count": 0
}
```

**Logique** :
- Tentative 1 : `retry_count = 0` → Si invalide, relance
- Tentative 2 : `retry_count = 1` → Si invalide, abandon
- Reset à 0 après chaque étape réussie

**Avantages** :
- Évite les boucles infinies
- Offre une 2ème chance à l'utilisateur
- Messages progressifs (1er : simple, 2ème : détaillé)

---

### 3. **Gestion d'erreurs HTTP améliorée**

Tous les appels HTTP ont :
```json
{
  "timeout": 10000,
  "transitions": {
    "success": "next_step",
    "failed": "handle_api_error"
  }
}
```

**Types d'erreurs gérées** :
- ⏱️ **Timeout** : 10 secondes max → Message "réessayer"
- 🔴 **HTTP 500** : Erreur serveur → Message "erreur technique"
- 📡 **Network failure** : Pas de connexion → Message "erreur technique"

**Messages contextuels** :
```
⚠️ Une erreur technique est survenue.

Merci de réessayer dans quelques instants.

Si le problème persiste, contacte notre support.

Désolé pour la gêne ! 🙏
```

---

### 4. **Validation stricte des entrées**

#### Opt-in validation
```
Accepté : OUI, O, YES, Y, OK, 1
Refusé : NON, N, NO, 0
Stop : STOP, ARRET, UNSUBSCRIBE
Invalide : Tout autre caractère → Relance
```

#### Nom validation
```
Valide : >= 2 caractères
Invalide : < 2 caractères → Message avec exemples
```

#### Menu validation
```
Valide : 1, 2, 3, 4, 5 ou MATCHS, PRONOSTIC, etc.
Invalide : Message avec liste complète des options
```

---

### 5. **Logging exhaustif**

Tous les événements sont loggés dans l'API :

| Événement | Endpoint | Payload |
|-----------|----------|---------|
| Scan initial | `/api/can/scan` | phone, source_type, source_detail, timestamp |
| Opt-in accepté | `/api/can/optin` | phone, timestamp |
| Inscription complète | `/api/can/inscription` | phone, name, source, timestamp |
| Refus | `/api/can/refus` | phone, timestamp |
| STOP | `/api/can/stop` | phone, timestamp |
| Réactivation | `/api/can/reactivate` | phone, timestamp |
| Abandon | `/api/can/abandon` | phone, timestamp |
| Timeout | `/api/can/timeout` | phone, timestamp |
| Erreur | `/api/can/error` | phone, error, timestamp |

---

## 🎯 Routage Intelligent

### Logique de décision

```
Message reçu
  ↓
STOP ? → Traiter STOP (priorité absolue)
  ↓
Vérifier statut utilisateur
  ↓
  ├─→ NOT_FOUND
  │     └─→ Peu importe le message → INSCRIPTION OBLIGATOIRE
  │
  ├─→ STOP
  │     └─→ Proposition réactivation
  │
  └─→ INSCRIT
        └─→ Analyser le mot-clé
              ├─→ MENU/AIDE → Menu
              ├─→ MATCHS/1 → Subflow matchs
              ├─→ PRONOSTIC/2 → Subflow pronostic
              ├─→ MES PRONOS/3 → Subflow mes pronos
              ├─→ CLASSEMENT/4 → Subflow classement
              ├─→ QUIZ/5 → Subflow quiz
              └─→ Autre → Message bienvenue + Menu
```

---

## 🚨 Gestion des cas limites

### 1. **STOP envoyé pendant l'inscription**

**Scénario** :
```
User: START_AFF_GOMBE
Bot: "Tape OUI pour t'inscrire"
User: STOP
```

**Comportement** :
- ✅ Détection immédiate du mot STOP
- ✅ Appel API `/stop`
- ✅ Message de confirmation
- ✅ Fin du flow

---

### 2. **STOP envoyé pendant la saisie du nom**

**Scénario** :
```
Bot: "Quel est ton nom ?"
User: STOP
```

**Comportement** :
- ✅ Priorité à STOP même pendant saisie
- ✅ Inscription abandonnée
- ✅ Statut STOP enregistré

---

### 3. **Timeout pendant l'inscription**

**Scénario** :
```
Bot: "Tape OUI pour t'inscrire"
User: (pas de réponse pendant 1h)
```

**Comportement** :
- ✅ Timeout après 3600 secondes
- ✅ Log API avec statut TIMEOUT
- ✅ Message explicatif
- ✅ Fin gracieuse

---

### 4. **Erreurs réseau**

**Scénario** :
```
Bot: Appel API check-user
API: (timeout ou 500)
```

**Comportement** :
- ✅ Détection de l'erreur
- ✅ Log de l'erreur dans l'API
- ✅ Message utilisateur clair
- ✅ Fin avec statut ERROR

---

### 5. **Messages avec espaces/formatage bizarre**

**Scénario** :
```
User: "  P  r  o  n  o  s  t  i  c  "
```

**Comportement** :
- ✅ Normalisation : "PRONOSTIC"
- ✅ Routage vers subflow pronostic
- ✅ Fonctionne parfaitement

---

## 📊 Statuts de fin possibles

| Statut | Description | Utilisateur peut revenir ? |
|--------|-------------|----------------------------|
| `SUCCESS` | Inscription ou action réussie | ✅ Oui |
| `ALREADY_REGISTERED` | Déjà inscrit, redirection vers menu | ✅ Oui |
| `REACTIVATED` | Réactivation réussie | ✅ Oui |
| `STOP` | Désabonnement volontaire | ✅ Oui (réactivation) |
| `REFUS` | Refus opt-in | ✅ Oui (nouveau message) |
| `ABANDON` | Trop de tentatives invalides | ✅ Oui (nouveau message) |
| `TIMEOUT` | Pas de réponse | ✅ Oui (nouveau message) |
| `ERROR` | Erreur technique | ✅ Oui (nouveau message) |

---

## 🔄 Subflows appelés

### Subflows à créer/configurer

1. **FWf255f47348477f7b361f4b7df59d5fd5** - Subflow Villages/Matchs
2. **FW26cc752ab63630c73404fab72632f65c** - Subflow Pronostic
3. **FW_MES_PRONOS_SID_HERE** - Subflow Mes Pronostics (à remplacer)
4. **FW_CLASSEMENT_SID_HERE** - Subflow Classement (à remplacer)
5. **FW6643799ed631c2c6a966923e94e11cce** - Subflow Quiz

**Paramètres passés aux subflows** :
```json
{
  "phone_number": "{{flow.variables.phone_number}}",
  "user_name": "{{widgets.http_check_user_status.parsed.name}}"
}
```

---

## 🛠️ Configuration requise

### Endpoints API nécessaires

Tous les endpoints doivent répondre en **JSON** :

1. **POST** `/api/can/check-user`
   ```json
   Request: { "phone": "whatsapp:+243..." }
   Response: {
     "status": "NOT_FOUND|INSCRIT|STOP",
     "name": "Nom utilisateur" (si INSCRIT ou STOP)
   }
   ```

2. **POST** `/api/can/scan`
   ```json
   Request: {
     "phone": "whatsapp:+243...",
     "source_type": "AFFICHE|PDV|DIGITAL|FLYER|RADIO|DIRECT",
     "source_detail": "GOMBE|MASINA|...",
     "timestamp": "2025-01-15 10:30:00",
     "status": "SCAN"
   }
   Response: { "success": true }
   ```

3. **POST** `/api/can/optin`
4. **POST** `/api/can/inscription`
5. **POST** `/api/can/refus`
6. **POST** `/api/can/stop`
7. **POST** `/api/can/reactivate`
8. **POST** `/api/can/abandon`
9. **POST** `/api/can/timeout`
10. **POST** `/api/can/error`

---

## ✅ Avantages de ce flow optimisé

### 1. **Expérience utilisateur améliorée**
- Messages clairs et contextuels
- 2ème chance en cas d'erreur de saisie
- Guidance explicite (exemples, format attendu)

### 2. **Robustesse technique**
- Gestion exhaustive des erreurs
- Timeouts configurés sur tous les appels API
- Retry logic pour éviter les frustrations

### 3. **Sécurité et contrôle**
- Limitation des tentatives (anti-spam)
- STOP prioritaire à tout moment
- Logging complet pour audit

### 4. **Maintenabilité**
- Code clair et bien structuré
- Séparation des responsabilités
- Facile à déboguer

### 5. **Flexibilité**
- Facile d'ajouter de nouveaux mots-clés
- Subflows modulaires
- Configuration centralisée

---

## 🚀 Prochaines étapes

1. **Importer le flow dans Twilio Studio**
   - Copier le contenu de `twilio_studio_flow_OPTIMIZED.json`
   - Créer un nouveau flow dans Twilio Studio
   - Coller et valider

2. **Configurer les SIDs des subflows**
   - Remplacer `FW_MES_PRONOS_SID_HERE` par le vrai SID
   - Remplacer `FW_CLASSEMENT_SID_HERE` par le vrai SID

3. **Tester tous les scénarios**
   - ✅ Nouvelle inscription (toutes sources)
   - ✅ Utilisateur déjà inscrit
   - ✅ Réactivation après STOP
   - ✅ STOP à différents moments
   - ✅ Erreurs de saisie (opt-in, nom, menu)
   - ✅ Timeouts
   - ✅ Tous les mots-clés du menu

4. **Monitorer les logs API**
   - Vérifier que tous les événements sont bien loggés
   - Analyser les abandons pour améliorer les messages
   - Suivre les erreurs API pour optimiser

---

## 📞 Support

Pour toute question sur ce flow, référez-vous aux fichiers :
- `twilio_studio_flow_OPTIMIZED.json` - Flow complet
- `FLOW_OPTIMIZED_DOCUMENTATION.md` - Cette documentation
- `PRONOSTIC_WHATSAPP_INTEGRATION.md` - Documentation pronostics

---

**Dernière mise à jour** : 2025-01-15
**Version** : 2.0 Optimisé
**Auteur** : Claude Code
