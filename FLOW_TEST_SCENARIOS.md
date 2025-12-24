# 🧪 Guide de Test - Flow Optimisé Solibra CAN 2025

## 📋 Checklist de Tests

Utilisez ce guide pour valider que le flow optimisé fonctionne correctement dans tous les scénarios.

---

## 🎯 Tests Nouveaux Utilisateurs (NOT_FOUND)

### ✅ Test 1 : Inscription complète avec source AFFICHE
**Message initial** : `START_AFF_GOMBE`

**Flow attendu** :
```
1. Bot: "⚽ BabiFoot by Solibra 2025... Tape OUI pour t'inscrire"
2. User: OUI
3. Bot: "Parfait ! Quel est ton nom ou pseudo ?"
4. User: Jean
5. Bot: "✅ C'est bon Jean ! Tu fais partie de la TEAM SOLIBRA..."
6. Bot: "🎯 MENU PRINCIPAL Que veux-tu faire ?..."
```

**Vérifications** :
- [ ] API `/check-user` appelée
- [ ] API `/scan` appelée avec source_type=AFFICHE, source_detail=GOMBE
- [ ] API `/optin` appelée
- [ ] API `/inscription` appelée avec name=Jean
- [ ] Menu affiché après inscription
- [ ] User peut maintenant utiliser les mots-clés (MATCHS, PRONOSTIC, etc.)

---

### ✅ Test 2 : Inscription avec source PDV
**Message initial** : `START_PDV_BRACONGO`

**Vérifications** :
- [ ] source_type = PDV_BRACONGO
- [ ] source_detail = BRACONGO
- [ ] Inscription complète fonctionne

---

### ✅ Test 3 : Inscription avec source DIGITAL
**Message initial** : `START_FB`

**Vérifications** :
- [ ] source_type = DIGITAL
- [ ] source_detail = FB
- [ ] Inscription complète fonctionne

---

### ✅ Test 4 : Inscription DIRECT (sans code)
**Message initial** : `Bonjour` (n'importe quel message)

**Vérifications** :
- [ ] source_type = DIRECT
- [ ] source_detail = SANS_QR
- [ ] Message de bienvenue affiché
- [ ] Inscription peut continuer normalement

---

### ✅ Test 5 : Refus opt-in (NON)
**Message initial** : `START_AFF_GOMBE`

**Flow** :
```
1. Bot: "Tape OUI pour t'inscrire"
2. User: NON
3. Bot: "Pas de problème ! Si tu changes d'avis..."
4. Flow se termine
```

**Vérifications** :
- [ ] API `/refus` appelée
- [ ] Message de refus affiché
- [ ] Flow se termine proprement (statut REFUS)

---

### ✅ Test 6 : Erreur de saisie opt-in avec relance
**Message initial** : `START_AFF_GOMBE`

**Flow** :
```
1. Bot: "Tape OUI pour t'inscrire"
2. User: PEUT ETRE
3. Bot: "🤔 Je n'ai pas bien compris... Tape OUI pour confirmer"
4. User: OUI
5. Bot: "Parfait ! Quel est ton nom ?"
6. Continue normalement
```

**Vérifications** :
- [ ] Première erreur → Relance avec message explicatif
- [ ] Deuxième tentative acceptée
- [ ] retry_count incrémenté puis reset
- [ ] Inscription continue

---

### ✅ Test 7 : Double erreur opt-in → Abandon
**Message initial** : `START_AFF_GOMBE`

**Flow** :
```
1. Bot: "Tape OUI pour t'inscrire"
2. User: BLABLA
3. Bot: "🤔 Je n'ai pas bien compris..."
4. User: NIMPORTE QUOI
5. Bot: "⏱️ Trop de tentatives... Si tu veux t'inscrire, envoie OUI"
6. Flow se termine
```

**Vérifications** :
- [ ] API `/abandon` appelée après 2 tentatives
- [ ] Message d'abandon clair
- [ ] Flow se termine (statut ABANDON)

---

### ✅ Test 8 : Nom trop court avec relance
**Message initial** : `START_AFF_GOMBE`

**Flow** :
```
1. Bot: "Tape OUI"
2. User: OUI
3. Bot: "Quel est ton nom ?"
4. User: J
5. Bot: "❌ Nom trop court ! Donne-moi ton prénom (minimum 2 lettres)"
6. User: Jean
7. Bot: "✅ C'est bon Jean !"
```

**Vérifications** :
- [ ] Validation : nom < 2 caractères → Relance
- [ ] Message avec exemples affiché
- [ ] Deuxième tentative acceptée
- [ ] Inscription complète

---

### ✅ Test 9 : STOP pendant opt-in
**Message initial** : `START_AFF_GOMBE`

**Flow** :
```
1. Bot: "Tape OUI pour t'inscrire"
2. User: STOP
3. Bot: "✅ C'est noté. Tu es désinscrit(e)..."
4. Flow se termine
```

**Vérifications** :
- [ ] STOP détecté immédiatement
- [ ] API `/stop` appelée
- [ ] Message de confirmation STOP
- [ ] Inscription abandonnée
- [ ] Statut STOP

---

### ✅ Test 10 : STOP pendant saisie du nom
**Message initial** : `START_AFF_GOMBE`

**Flow** :
```
1. Bot: "Tape OUI"
2. User: OUI
3. Bot: "Quel est ton nom ?"
4. User: STOP
5. Bot: "✅ C'est noté. Tu es désinscrit(e)..."
```

**Vérifications** :
- [ ] STOP prioritaire même pendant saisie
- [ ] API `/stop` appelée
- [ ] Inscription abandonnée

---

### ✅ Test 11 : Timeout pendant opt-in
**Message initial** : `START_AFF_GOMBE`

**Flow** :
```
1. Bot: "Tape OUI pour t'inscrire"
2. User: (pas de réponse pendant 1h)
3. Bot: "⏱️ Temps écoulé ! Pour recommencer, envoie-nous un message"
```

**Vérifications** :
- [ ] Timeout après 3600 secondes
- [ ] API `/timeout` appelée
- [ ] Message timeout clair
- [ ] Statut TIMEOUT

---

## 👤 Tests Utilisateurs INSCRITS

### ✅ Test 12 : Utilisateur inscrit envoie un message aléatoire
**Précondition** : User déjà inscrit (status=INSCRIT)
**Message** : `Salut`

**Flow** :
```
1. Bot: "👋 Salut Jean ! Content de te revoir ! Tu es déjà inscrit..."
2. Bot: "🎯 MENU PRINCIPAL..."
```

**Vérifications** :
- [ ] API `/check-user` retourne INSCRIT avec name
- [ ] Message de bienvenue personnalisé
- [ ] Menu affiché automatiquement

---

### ✅ Test 13 : Mot-clé MENU
**Précondition** : User inscrit
**Messages à tester** : `MENU`, `menu`, `Menu`, `AIDE`, `HELP`, `INFO`

**Vérifications** :
- [ ] Tous affichent le menu principal
- [ ] Normalisation fonctionne (majuscules/minuscules)

---

### ✅ Test 14 : Mot-clé MATCHS
**Précondition** : User inscrit
**Messages à tester** : `MATCHS`, `matchs`, `MATCH`, `1`

**Flow** :
```
1. User: MATCHS
2. Bot: Appel subflow matchs (FWf255...)
3. Subflow s'exécute
```

**Vérifications** :
- [ ] Subflow matchs appelé
- [ ] Paramètres phone_number et user_name passés
- [ ] Subflow s'exécute correctement

---

### ✅ Test 15 : Mot-clé PRONOSTIC
**Précondition** : User inscrit
**Messages à tester** : `PRONOSTIC`, `PRONO`, `prono`, `2`

**Vérifications** :
- [ ] Subflow pronostic appelé (FW26cc...)
- [ ] Paramètres passés correctement

---

### ✅ Test 16 : Mot-clé MES PRONOS
**Précondition** : User inscrit
**Messages à tester** : `MES PRONOS`, `MESPRONOS`, `MESPRONO`, `3`

**Vérifications** :
- [ ] Subflow mes pronos appelé
- [ ] Gestion des espaces dans "MES PRONOS"

---

### ✅ Test 17 : Mot-clé CLASSEMENT
**Précondition** : User inscrit
**Messages à tester** : `CLASSEMENT`, `RANKING`, `LEADERBOARD`, `4`

**Vérifications** :
- [ ] Subflow classement appelé

---

### ✅ Test 18 : Mot-clé QUIZ
**Précondition** : User inscrit
**Messages à tester** : `QUIZ`, `quiz`, `5`, `SOLIBRAVILLAGECAN`

**Vérifications** :
- [ ] Subflow quiz appelé (FW664...)

---

### ✅ Test 19 : Choix invalide dans le menu
**Précondition** : User inscrit, menu affiché

**Flow** :
```
1. Bot: "🎯 MENU PRINCIPAL..."
2. User: 9
3. Bot: "❌ Choix invalide ! Merci de choisir entre 1 et 5..."
4. Menu réaffiché
```

**Vérifications** :
- [ ] Message d'erreur explicatif
- [ ] Liste des options rappelée
- [ ] Menu réaffiché automatiquement

---

### ✅ Test 20 : STOP utilisateur inscrit
**Précondition** : User inscrit

**Flow** :
```
1. User: STOP
2. Bot: "✅ C'est noté. Tu es désinscrit(e)..."
```

**Vérifications** :
- [ ] API `/stop` appelée
- [ ] Message de confirmation
- [ ] Statut changé à STOP

---

### ✅ Test 21 : Erreur dans un subflow
**Précondition** : User inscrit
**Simulation** : Subflow retourne "failed"

**Flow** :
```
1. User: PRONOSTIC
2. Subflow échoue
3. Bot: "⚠️ Une erreur est survenue... Tape MENU pour voir les options"
4. Menu réaffiché
```

**Vérifications** :
- [ ] Erreur subflow capturée
- [ ] Message d'erreur affiché
- [ ] Retour au menu

---

## 🔄 Tests Utilisateurs STOP (Réactivation)

### ✅ Test 22 : Réactivation réussie
**Précondition** : User avec status=STOP
**Message** : `Bonjour` (n'importe quoi)

**Flow** :
```
1. API check-user → status=STOP, name=Jean
2. Bot: "👋 Salut Jean ! Tu t'étais désinscrit... Tape OUI pour revenir"
3. User: OUI
4. Bot: "🎉 Content de te revoir Jean !..."
5. Bot: "🎯 MENU PRINCIPAL..."
```

**Vérifications** :
- [ ] Message personnalisé avec nom
- [ ] API `/reactivate` appelée
- [ ] Message de succès
- [ ] Menu affiché
- [ ] User peut maintenant utiliser les fonctionnalités

---

### ✅ Test 23 : Refus de réactivation
**Précondition** : User STOP

**Flow** :
```
1. Bot: "Tape OUI pour revenir"
2. User: NON
3. Bot: "OK, pas de souci ! Tu restes désinscrit..."
```

**Vérifications** :
- [ ] Message "reste désinscrit"
- [ ] Statut reste STOP
- [ ] Flow se termine

---

### ✅ Test 24 : Erreur réactivation puis correction
**Précondition** : User STOP

**Flow** :
```
1. Bot: "Tape OUI pour revenir"
2. User: PEUT ETRE
3. Bot: "🤔 Je n'ai pas compris. Tape OUI ou NON"
4. User: OUI
5. Bot: "🎉 Content de te revoir !"
```

**Vérifications** :
- [ ] Réponse invalide → Message d'erreur
- [ ] Pas de limite de retry (continue jusqu'à réponse valide)
- [ ] Réactivation fonctionne après correction

---

## 🚨 Tests Gestion d'Erreurs

### ✅ Test 25 : Erreur API check-user (timeout)
**Simulation** : API ne répond pas

**Flow** :
```
1. User: START_AFF_GOMBE
2. API timeout après 10 secondes
3. Bot: "⚠️ Une erreur technique est survenue. Merci de réessayer..."
```

**Vérifications** :
- [ ] Timeout après 10 secondes
- [ ] Message d'erreur technique
- [ ] API `/error` appelée avec error="API_ERROR"
- [ ] Statut ERROR

---

### ✅ Test 26 : Erreur API inscription
**Simulation** : API inscription retourne 500

**Flow** :
```
1. User complète inscription
2. API `/inscription` échoue
3. Bot: "⚠️ Une erreur technique est survenue..."
```

**Vérifications** :
- [ ] Erreur capturée
- [ ] Message utilisateur affiché
- [ ] Log erreur

---

### ✅ Test 27 : Delivery failure (WhatsApp)
**Simulation** : Numéro invalide ou bloqué

**Flow** :
```
1. Bot tente d'envoyer message
2. deliveryFailure event
3. API `/error` appelée avec status=DELIVERY_FAILURE
```

**Vérifications** :
- [ ] Event capturé
- [ ] Log dans API
- [ ] Flow termine gracieusement

---

## 🧹 Tests Normalisation

### ✅ Test 28 : Message avec espaces
**Messages à tester** :
- `  PRONOSTIC  `
- `P R O N O S T I C`
- `   m e n u   `

**Vérifications** :
- [ ] Tous normalisés correctement
- [ ] Routage fonctionne
- [ ] Subflow correspondant appelé

---

### ✅ Test 29 : Message avec majuscules/minuscules
**Messages à tester** :
- `MeNu`
- `pRoNoStIc`
- `MeS pRoNoS`

**Vérifications** :
- [ ] Normalisation en majuscules
- [ ] Routage correct

---

### ✅ Test 30 : Message avec sauts de ligne
**Message** :
```
PRONO
STIC
```

**Vérifications** :
- [ ] Sauts de ligne supprimés
- [ ] Devient "PRONOSTIC"
- [ ] Routage correct

---

## 📊 Tests Sources d'Acquisition

### ✅ Test 31 : Toutes les sources AFFICHE
**Messages** :
- `START_AFF_GOMBE`
- `START_AFF_MASINA`
- `START_AFF_LEMBA`
- `START_AFF_BANDAL`
- `START_AFF_NGALI`
- `START_AFF_MATETE`
- `START_AFF_KINTAMBO`
- `START_AFF_NDJILI`
- `START_AFF_LIMETE`

**Vérifications pour chaque** :
- [ ] source_type = AFFICHE
- [ ] source_detail = [GOMBE|MASINA|...]
- [ ] Inscription fonctionne

---

### ✅ Test 32 : Sources PDV
**Messages** :
- `START_PDV_BRACONGO`
- `START_PDV_BAR1`
- `START_PDV_DEPOT1`

**Vérifications** :
- [ ] source_type = PDV_BRACONGO
- [ ] source_detail extrait correctement

---

### ✅ Test 33 : Sources DIGITAL
**Messages** :
- `START_FB`
- `START_IG`
- `START_TIKTOK`
- `START_WA_STATUS`
- `START_WEB`

**Vérifications** :
- [ ] source_type = DIGITAL
- [ ] source_detail = [FB|IG|TIKTOK|WA_STATUS|WEB]

---

### ✅ Test 34 : Sources FLYER
**Messages** :
- `START_FLYER_UNI`
- `START_FLYER_RUE`
- `START_FLYER_EVENT`

**Vérifications** :
- [ ] source_type = FLYER
- [ ] source_detail correctement extrait

---

### ✅ Test 35 : Sources RADIO
**Messages** :
- `START_RADIO`
- `START_RTGA`
- `START_RTNC`

**Vérifications** :
- [ ] source_type = RADIO
- [ ] source_detail correctement extrait

---

## 🎯 Résumé des Statuts à Vérifier

Après chaque test, vérifier le statut final :

| Statut | Tests concernés |
|--------|-----------------|
| `SUCCESS` | 1-4, 6, 8, 12-18, 22, 28-35 |
| `ALREADY_REGISTERED` | 12 |
| `REACTIVATED` | 22, 24 |
| `STOP` | 9, 10, 20 |
| `REFUS` | 5, 23 |
| `ABANDON` | 7 |
| `TIMEOUT` | 11 |
| `ERROR` | 25, 26, 27 |

---

## 🛠️ Outils de Test

### Twilio Console
1. Aller dans Twilio Console → Studio → Votre Flow
2. Cliquer sur "Test" en haut à droite
3. Entrer un numéro de test : `whatsapp:+243999999999`
4. Envoyer les messages de test

### Postman Collection
Utiliser la collection existante :
```
C:\wamp64\www\YESWECANGE\Solibra-activation\CAN_2025_Postman_Collection.json
```

Tests API à faire en parallèle :
- ✅ POST `/api/can/check-user`
- ✅ POST `/api/can/scan`
- ✅ POST `/api/can/optin`
- ✅ POST `/api/can/inscription`
- ✅ Etc.

---

## 📈 Métriques à Surveiller

Pendant les tests, monitorer :

1. **Logs API** (`storage/logs/laravel.log`)
   ```bash
   tail -f storage/logs/laravel.log
   ```

2. **Base de données**
   - Table `users` : Vérifier les nouveaux users
   - Table `conversation_sessions` : États de conversation
   - Table `message_logs` : Tous les messages loggés

3. **Twilio Studio Debugger**
   - Vérifier les transitions
   - Variables flow à chaque étape
   - Appels HTTP (request/response)

---

## ✅ Checklist Complète

- [ ] 35 tests passés avec succès
- [ ] Tous les statuts de fin testés
- [ ] Toutes les sources d'acquisition testées
- [ ] Normalisation validée
- [ ] Gestion d'erreurs validée
- [ ] Timeouts testés
- [ ] Subflows tous appelés correctement
- [ ] Logs API complets et corrects
- [ ] Aucune boucle infinie détectée
- [ ] Messages utilisateur clairs et en français

---

## 🎓 Best Practices

1. **Tester avec de vrais numéros WhatsApp** (pas seulement la console)
2. **Vérifier les logs API** après chaque test
3. **Nettoyer la base de données** entre les tests si nécessaire
4. **Tester les cas limites** (très long nom, caractères spéciaux, etc.)
5. **Monitorer les performances** (temps de réponse API)

---

**Date de création** : 2025-01-15
**Version** : 1.0
**Auteur** : Claude Code
