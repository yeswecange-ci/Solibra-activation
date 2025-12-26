# Corrections des Bugs du Flow Intelligent V2

## 🐛 Bugs identifiés et corrigés

### Bug Critique #1 : Variables incorrectes dans `check_what_is_missing`

**Emplacement :** State `check_what_is_missing`

**Problème :**
Dans votre flow, TOUTES les conditions utilisaient la variable `has_boisson_preferee` au lieu d'utiliser les bonnes variables :

```json
// ❌ INCORRECT (votre flow actuel)
{
  "name": "check_what_is_missing",
  "transitions": [
    {
      "next": "msg_question_1",
      "conditions": [{
        "arguments": ["{{widgets.http_check_user.parsed.has_boisson_preferee}}"]
      }]
    },
    {
      "next": "msg_question_2",
      "conditions": [{
        "arguments": ["{{widgets.http_check_user.parsed.has_boisson_preferee}}"]  // BUG!
      }]
    },
    {
      "next": "msg_pdf_validation",
      "conditions": [{
        "arguments": ["{{widgets.http_check_user.parsed.has_boisson_preferee}}"]  // BUG!
      }]
    }
  ]
}
```

**Correction :**
```json
// ✅ CORRECT (flow corrigé)
{
  "name": "check_what_is_missing",
  "transitions": [
    {
      "next": "msg_question_1",
      "conditions": [{
        "arguments": ["{{widgets.http_check_user.parsed.has_boisson_preferee}}"]  // Correct
      }]
    },
    {
      "next": "msg_question_2",
      "conditions": [{
        "arguments": ["{{widgets.http_check_user.parsed.has_quiz_answer}}"]  // Corrigé!
      }]
    },
    {
      "next": "msg_pdf_validation",
      "conditions": [{
        "arguments": ["{{widgets.http_check_user.parsed.has_accepted_policies}}"]  // Corrigé!
      }]
    }
  ]
}
```

**Impact :**
- Le flow ne pouvait pas détecter correctement ce qui manquait
- Les utilisateurs voyaient toujours les mêmes questions
- Les réponses n'étaient pas prises en compte pour la reprise

---

### Bug Critique #2 : Ordre des transitions noMatch

**Problème :**
Le `noMatch` pointait vers `msg_question_2` au lieu de gérer le cas où tout est complété.

**Correction :**
```json
// ❌ AVANT
{
  "transitions": [
    {
      "next": "msg_question_2",  // Mauvais!
      "event": "noMatch"
    },
    // ...
  ]
}

// ✅ APRÈS
{
  "transitions": [
    {
      "next": "msg_pdf_validation",  // Si rien ne manque, aller au PDF
      "event": "noMatch"
    },
    // ...
  ]
}
```

---

## 📊 Scénarios de test pour vérifier la correction

### Test 1 : Nouvel utilisateur (n'existe pas en BD)
```
User: "Bonjour"
→ check-user → status = NOT_FOUND
→ send_message_1 (message de bienvenue) ✓
→ msg_question_1 (boisson) ✓
→ User répond "BOCK"
→ http_save_answer_1 (sauvegarde en BD) ✓
→ msg_question_2 (quiz) ✓
→ User répond "OUI"
→ http_save_answer_2 (sauvegarde en BD) ✓
→ msg_pdf_validation ✓
→ User répond "OUI"
→ http_save_policies (sauvegarde en BD) ✓
→ msg_confirmation_finale ✓
```

### Test 2 : Utilisateur incomplet (a répondu Q1, manque Q2)
```
User: "Salut"
→ check-user → status = INCOMPLETE
→ has_boisson_preferee = true ✓
→ has_quiz_answer = false ✓
→ has_accepted_policies = false ✓
→ check_what_is_missing
→ SAUTE msg_question_1 (car has_boisson = true) ✓
→ VA DIRECTEMENT À msg_question_2 ✓
→ Continue normalement
```

### Test 3 : Utilisateur ayant tout complété
```
User: "Hello"
→ check-user → status = COMPLETE
→ msg_already_complete ✓
→ Affiche résumé avec dates ✓
→ FIN (ne pose aucune question) ✓
```

---

## 🔍 Comment vérifier que votre API fonctionne

Testez l'endpoint `check-user` avec curl :

```bash
# Test 1 : Utilisateur qui n'existe pas
curl -X POST https://app-can-solibra.ywcdigital.com/api/can/check-user \
  -H "Content-Type: application/json" \
  -d '{"phone": "+2250700000000"}'

# Réponse attendue :
# {"status": "NOT_FOUND", "message": "User not found"}

# Test 2 : Créer un utilisateur avec seulement boisson
curl -X POST https://app-can-solibra.ywcdigital.com/api/can/inscription-simple \
  -H "Content-Type: application/json" \
  -d '{"phone": "+2250700000000", "answer_1": "BOCK"}'

# Test 3 : Vérifier son statut (devrait être INCOMPLETE)
curl -X POST https://app-can-solibra.ywcdigital.com/api/can/check-user \
  -H "Content-Type: application/json" \
  -d '{"phone": "+2250700000000"}'

# Réponse attendue :
# {
#   "status": "INCOMPLETE",
#   "has_boisson_preferee": true,
#   "has_quiz_answer": false,
#   "has_accepted_policies": false,
#   "boisson_preferee": "BOCK",
#   "quiz_answer": null,
#   ...
# }
```

---

## 📝 Checklist de déploiement

1. **Vérifier l'API Laravel** :
   ```bash
   php artisan migrate  # S'assurer que les colonnes existent
   ```

2. **Vérifier qu'il y a un village actif** :
   ```bash
   php artisan tinker
   >>> Village::where('is_active', true)->count()
   # Doit retourner au moins 1
   ```

3. **Importer le nouveau flow dans Twilio Studio** :
   - Ouvrir votre flow existant
   - Cliquer sur "Import from JSON"
   - Copier le contenu de `flow_intelligent_v2_production.json`
   - Coller et importer
   - **PUBLIER le flow**

4. **Tester avec un nouveau numéro** :
   - Envoyer "Bonjour" depuis WhatsApp
   - Vérifier que vous recevez le message de bienvenue
   - Répondre "BOCK" à la question 1
   - Vérifier en BD que `boisson_preferee` = "BOCK"
   - Fermer WhatsApp
   - Renvoyer "Salut" depuis WhatsApp
   - **IMPORTANT** : Vous devez recevoir directement la question 2 (PAS la question 1)

5. **Vérifier les logs Laravel** :
   ```bash
   tail -f storage/logs/laravel.log
   ```
   Vous devriez voir :
   ```
   [2025-12-26] Twilio Studio - New user registered (simple flow)
   [2025-12-26] Twilio Studio - User updated (simple flow)
   ```

---

## 🚨 Si le problème persiste

### Vérifier que l'API retourne bien les booléens

Dans Twilio Studio Debugger, vérifiez la réponse de `http_check_user` :

```json
{
  "status": "INCOMPLETE",
  "has_boisson_preferee": false,  // Doit être un booléen, pas une string
  "has_quiz_answer": false,
  "has_accepted_policies": false
}
```

Si vous voyez `"false"` (avec guillemets), c'est une string. L'API est correcte selon le code que j'ai vérifié.

### Vérifier les logs Twilio

1. Aller sur Twilio Console
2. Monitor > Logs > Debugger
3. Filtrer par votre numéro WhatsApp
4. Regarder les transitions du flow
5. Vérifier quelle condition est matchée dans `check_what_is_missing`

---

## 📦 Fichiers fournis

1. **`flow_intelligent_v2_production.json`** - Flow corrigé avec URL de production
2. **`BUG_FIXES.md`** - Ce document
3. **`FLOW_DIFFERENCES.md`** - Comparaison avec l'ancien flow
4. **`FLOW_INTELLIGENT_GUIDE.md`** - Guide complet d'intégration

---

**Date de correction** : 26/12/2025
**Bugs critiques corrigés** : 2
**Compatibilité** : Laravel 12 + Twilio Studio
