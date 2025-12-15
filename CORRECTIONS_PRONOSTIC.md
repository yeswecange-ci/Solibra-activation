# 🐛 Corrections des Bugs de Pronostic

## Date: 15 décembre 2025

---

## 📋 Problèmes Signalés

1. **Un seul match s'affiche** alors que 2 matchs ont été créés dans le dashboard
2. **Aucun retour après placement d'un pronostic** - Le bot ne répond pas après qu'un utilisateur fasse son pronostic

---

## ✅ Bugs Identifiés et Corrigés

### Bug #1: Filtre de 7 jours trop restrictif

**Fichier:** `app/Http/Controllers/Api/TwilioStudioController.php`
**Ligne:** 570, 576-577

**Problème:**
```php
$days = $request->input('days', 7); // Par défaut 7 jours
$matches = FootballMatch::where('match_date', '>=', $now)
    ->where('match_date', '<=', $endDate) // ❌ Filtre sur 7 jours
```

Le endpoint `getMatchesFormatted()` filtrait les matchs sur les 7 prochains jours seulement. Si un match était programmé dans 10 jours (comme "RDC vs Maroc" le 25/12), il n'apparaissait pas dans la liste.

**Solution:**
```php
$days = $request->input('days', 30); // ✅ 30 jours au lieu de 7
$matches = FootballMatch::where('match_date', '>=', $now)
    ->where('match_date', '<=', $endDate)
    ->where('pronostic_enabled', true) // ✅ Filtre ajouté
```

**Changements:**
- Augmentation de la période de 7 à 30 jours
- Ajout du filtre `pronostic_enabled = true` pour n'afficher que les matchs avec pronostics actifs

---

### Bug #2: Logique inversée dans canBet()

**Fichier:** `app/Models/Pronostic.php`
**Ligne:** 80-82

**Problème:**
```php
// ❌ BUG: Cette condition bloque TOUS les pronostics futurs
if ($match->match_date->diffInMinutes(now(), false) < 5) {
    return false;
}
```

**Explication du bug:**
- `$match->match_date->diffInMinutes(now(), false)` retourne un nombre **négatif** quand le match est dans le futur
- Exemple: Si le match est dans 14 heures, cela retourne `-840` minutes
- La condition `< 5` est donc toujours **vraie** pour les matchs futurs (car -840 < 5)
- **Résultat:** Tous les pronostics étaient bloqués avec le message "Ce match n'accepte plus de pronostics"

**Solution:**
```php
// ✅ FIX: Inversion de la logique
$minutesUntilMatch = now()->diffInMinutes($match->match_date, false);
if ($minutesUntilMatch < 5) {
    return false;
}
```

**Explication de la correction:**
- `now()->diffInMinutes($match->match_date, false)` retourne un nombre **positif** pour les matchs futurs
- Exemple: Si le match est dans 14 heures, cela retourne `+840` minutes
- La condition `< 5` bloque maintenant correctement les matchs dans moins de 5 minutes
- Les matchs dans le passé retournent un nombre négatif, donc sont aussi bloqués ✅

---

## 🧪 Tests Effectués

### Test 1: Affichage des matchs
```bash
GET /api/can/matches/formatted?limit=5
```

**Avant correction:**
```json
{
  "count": 1,
  "matches": [
    {
      "id": 2,
      "team_a": "Cote d'ivoire",
      "team_b": "Mali"
    }
  ]
}
```

**Après correction:**
```json
{
  "count": 2,
  "matches": [
    {
      "id": 2,
      "team_a": "Cote d'ivoire",
      "team_b": "Mali",
      "match_date": "15/12/2025"
    },
    {
      "id": 1,
      "team_a": "RDC",
      "team_b": "Maroc",
      "match_date": "25/12/2025"
    }
  ]
}
```

✅ **Les 2 matchs s'affichent maintenant**

---

### Test 2: Enregistrement de pronostic
```bash
POST /api/can/pronostic
phone=243828500007&match_id=1&prediction_type=team_a_win
```

**Avant correction:**
```json
{
  "success": false,
  "message": "Ce match n'accepte plus de pronostics."
}
// Status: 400
```

**Après correction:**
```json
{
  "success": true,
  "message": "✅ Pronostic enregistré !\n\nRDC vs Maroc\n🎯 Ton pronostic : Victoire RDC",
  "pronostic": {
    "id": 1,
    "match": "RDC vs Maroc",
    "prediction_type": "team_a_win",
    "prediction_text": "Victoire RDC"
  }
}
// Status: 200
```

✅ **Le pronostic est maintenant enregistré avec succès**

---

## 📱 Impact sur le Flow Twilio

### Avant les corrections

**Comportement utilisateur:**
1. L'utilisateur voit seulement 1 match sur 2 dans la liste
2. Il choisit un match et fait son pronostic
3. **Aucun message de retour** (car l'API retournait 400, déclenchant le widget `msg_erreur_prono` avec message générique)

### Après les corrections

**Comportement utilisateur:**
1. L'utilisateur voit **tous les matchs** disponibles (2 matchs)
2. Il choisit un match et fait son pronostic
3. **Il reçoit un message de confirmation:**
   ```
   ✅ Pronostic enregistré !

   RDC vs Maroc
   🎯 Ton pronostic : Victoire RDC
   ```

---

## 🔄 Actions Requises

### ⚠️ Important: Republier le Flow Twilio

Si vous avez modifié le flow Twilio dans Studio, **vous devez le republier** pour que les changements soient actifs :

1. Ouvrir Twilio Console → Studio → Flows
2. Sélectionner votre flow "CAN 2025"
3. Cliquer sur **"Publish"** en haut à droite
4. Confirmer la publication

**Note:** Les modifications du backend (Laravel) sont automatiquement actives, mais le flow Twilio doit être republié manuellement.

---

## 🎯 Configuration du Flow Twilio

Le flow est correctement configuré:

### Widget: `http_save_prono`
```
URL: https://can-wabracongo.ywcdigital.com/api/can/pronostic
Method: POST
Body: phone={{flow.variables.phone_number}}&match_id={{flow.variables.selected_match_id}}&prediction_type={{flow.variables.prediction_type}}
```

### Widget: `msg_confirmation_prono`
```
Body: {{widgets.http_save_prono.parsed.message}}
```

Ce widget affiche automatiquement le message retourné par l'API (le champ `message` de la réponse JSON).

### Widget: `msg_erreur_prono`
```
Body: Une erreur s'est produite. Réessaye plus tard !
```

S'affiche si l'API retourne un code d'erreur (400, 404, 500, etc.).

---

## 📊 Données de Test

### Matchs Créés
- **Match 1:** Cote d'ivoire vs Mali - 15/12/2025 à 20:00
- **Match 2:** RDC vs Maroc - 25/12/2025 à 16:00

Les deux ont `pronostic_enabled = true` et `status = scheduled`

### Pronostic Test Enregistré
```
ID: 1
User: Raoul (+243828500007)
Match: RDC vs Maroc
Prediction: team_a_win (Victoire RDC)
```

---

## ✅ Checklist de Vérification

- [x] Bug #1 corrigé: Les 2 matchs s'affichent
- [x] Bug #2 corrigé: Les pronostics peuvent être enregistrés
- [x] API testée: Endpoints fonctionnent correctement
- [x] Pronostic test enregistré en base de données
- [ ] **Flow Twilio republié** (À faire par vous)
- [ ] **Test end-to-end via WhatsApp** (À faire après republication)

---

## 🐛 Debugging

Si le problème persiste après ces corrections:

### 1. Vérifier les logs Laravel
```bash
tail -f storage/logs/laravel.log
```

### 2. Vérifier les logs Twilio
- Console Twilio → Monitor → Logs → Debugger
- Chercher les erreurs HTTP de votre flow

### 3. Tester l'API manuellement
```bash
# Tester la liste des matchs
curl "https://can-wabracongo.ywcdigital.com/api/can/matches/formatted?limit=5"

# Tester l'enregistrement d'un pronostic
curl -X POST "https://can-wabracongo.ywcdigital.com/api/can/pronostic" \
  -d "phone=243XXXXXXXXX" \
  -d "match_id=1" \
  -d "prediction_type=team_a_win"
```

### 4. Vérifier les variables du flow
Dans Twilio Studio Debugger, vérifier que ces variables sont bien définies:
- `flow.variables.phone_number`
- `flow.variables.selected_match_id`
- `flow.variables.prediction_type`

---

## 📞 Support

Pour toute question ou problème supplémentaire:
1. Consultez les logs Laravel et Twilio
2. Vérifiez que le flow est bien publié
3. Testez les endpoints API directement
4. Vérifiez la configuration des matchs (pronostic_enabled, status, date)
