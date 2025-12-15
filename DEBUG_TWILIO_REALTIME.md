# 🔍 Debug Twilio Flow en Temps Réel

## ✅ API Testée et Validée

**Résultat des tests:**
- ✅ L'API répond correctement (HTTP 200)
- ✅ Les pronostics sont enregistrés en base
- ✅ Le message de réponse est bien formaté

**Donc le problème n'est PAS l'API Laravel.**

---

## 🎯 Ce Qu'il Faut Vérifier Maintenant

Le widget Twilio `http_save_prono` est configuré en POST, mais **soit Twilio n'appelle pas l'API, soit la réponse n'est pas gérée correctement**.

---

## 📡 Test en Temps Réel

### Étape 1: Surveiller les Logs Laravel

**Ouvrir un terminal et lancer:**
```bash
cd C:\YESWECANGE\can-activation-kinshasa
tail -f storage/logs/laravel.log
```

**Laisser cette fenêtre ouverte.**

### Étape 2: Faire un Pronostic via WhatsApp

**Depuis WhatsApp:**
1. Envoyer un message au bot
2. Choisir un match (1 ou 2)
3. Choisir un pronostic (1, 2 ou 3)

### Étape 3: Observer les Logs

**Scénario A: Vous voyez des logs apparaître**
```
[2025-12-15 XX:XX:XX] production.INFO: === DÉBUT savePronostic ===
[2025-12-15 XX:XX:XX] production.INFO: Validation passed
[2025-12-15 XX:XX:XX] production.INFO: Twilio Studio - Pronostic saved (simple)
```

✅ **Twilio appelle bien l'API**
→ Le problème est dans l'affichage du message de retour
→ Passez à la Section "Fix Message Retour"

**Scénario B: Aucun log n'apparaît**
```
(rien du tout)
```

❌ **Twilio n'appelle PAS l'API**
→ Le problème est dans la configuration du widget
→ Passez à la Section "Fix Widget Configuration"

---

## 🔧 Fix Message Retour (Scénario A)

Si les logs montrent que l'API est appelée, mais vous ne recevez pas de message:

### Solution 1: Widget msg_confirmation_prono

1. **Ouvrir Twilio Studio** → Widget `msg_confirmation_prono`

2. **Vérifier le Body:**
   ```
   {{widgets.http_save_prono.parsed.message}}
   ```

3. **Si ça ne marche pas, essayer:**
   ```
   Ton pronostic a bien ete enregistre ! Merci.
   ```

4. **Sauvegarder et Publier**

### Solution 2: Vérifier les Transitions

1. Widget `http_save_prono`
2. **Transitions:**
   - `success` → `msg_confirmation_prono` ✅
   - `failed` → `msg_erreur_prono`

3. Si la transition pointe ailleurs → **Corriger**

---

## 🔧 Fix Widget Configuration (Scénario B)

Si aucun log n'apparaît, le widget n'appelle pas l'API:

### Vérification 1: URL Complète

**Widget `http_save_prono` → URL:**
```
https://can-wabracongo.ywcdigital.com/api/can/pronostic
```

⚠️ **Vérifier qu'il n'y a pas:**
- D'espace avant ou après
- De faute de frappe
- De http:// au lieu de https://

### Vérification 2: Method = POST

```
REQUEST METHOD: [POST ▼]
```

Pas GET, pas PUT, **POST**.

### Vérification 3: Content-Type

```
CONTENT TYPE: application/x-www-form-urlencoded
```

### Vérification 4: Body

```
phone={{flow.variables.phone_number}}&match_id={{flow.variables.selected_match_id}}&prediction_type={{flow.variables.prediction_type}}
```

**Vérifier que:**
- Les accolades sont bien doublées `{{...}}`
- Pas d'espace dans les noms de variables
- Les `&` séparent bien les paramètres

### Vérification 5: Variables Définies

**Les variables doivent être définies AVANT le widget `http_save_prono`:**

- `flow.variables.phone_number` → Défini au début du flow dans `set_phone`
- `flow.variables.selected_match_id` → Défini dans `set_match_1` à `set_match_5`
- `flow.variables.prediction_type` → Défini dans `set_prono_team_a/b/draw`

**Pour vérifier, ajouter un widget de debug AVANT `http_save_prono`:**

1. Créer widget `send-message` nommé `debug_vars`
2. Body:
   ```
   DEBUG:
   Phone: {{flow.variables.phone_number}}
   Match: {{flow.variables.selected_match_id}}
   Type: {{flow.variables.prediction_type}}
   ```
3. Transition: `debug_vars` → `http_save_prono`

**Si vous recevez ce message avec des valeurs:**
- ✅ Les variables sont OK
- Le problème est dans le widget HTTP lui-même

**Si les valeurs sont vides:**
- ❌ Les variables ne sont pas définies
- Vérifier les widgets `set_match_X` et `set_prono_X`

---

## 📊 Vérifier Twilio Debugger

1. **Twilio Console** → **Monitor** → **Logs** → **Debugger**
2. Filtrer par votre numéro: `+243828500007`
3. Chercher les erreurs HTTP

**Erreurs possibles:**

### Error 11200: HTTP Retrieval Failure
```
HTTP retrieval failure
```

**Causes:**
- L'URL n'est pas accessible depuis Twilio
- Le serveur ne répond pas

**Solution:**
- Vérifier que le serveur est en ligne
- Tester l'URL avec curl depuis un autre serveur

### Error 21211: Invalid 'To' Number
```
Invalid 'To' phone number
```

**Cause:**
- `{{contact.channel.address}}` n'est pas défini

**Solution:**
- Vérifier que le widget `msg_confirmation_prono` a bien:
  - `from: {{flow.channel.address}}`
  - `to: {{contact.channel.address}}`

---

## 🧪 Test Final

### Test 1: Vérifier que le Widget Appelle l'API

**Pendant que les logs sont ouverts:**
```bash
tail -f storage/logs/laravel.log
```

**Faire un pronostic via WhatsApp.**

**Vous DEVEZ voir:**
```
[XX:XX:XX] production.INFO: === DÉBUT savePronostic ===
{
  "all_data": {
    "phone": "+243828500007",
    "match_id": "2",
    "prediction_type": "team_a_win"
  },
  "method": "POST"  ← DOIT ÊTRE POST !
}
```

### Test 2: Vérifier en Base de Données

**Après chaque pronostic:**
```bash
php -r "
require 'vendor/autoload.php';
\$app = require_once 'bootstrap/app.php';
\$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();
echo 'Total: ' . App\Models\Pronostic::count() . PHP_EOL;
"
```

**Le nombre doit augmenter** si c'est un nouveau match.

### Test 3: Vérifier le Message WhatsApp

**Après le pronostic, vous DEVEZ recevoir:**
- Le message de confirmation
- OU le message d'erreur
- **PAS rien du tout**

---

## 📸 Captures d'Écran Utiles

**Envoyez-moi:**

1. **Widget `http_save_prono`** (configuration complète)
2. **Widget `msg_confirmation_prono`** (body du message)
3. **Logs Laravel** après un test
4. **Twilio Debugger** (s'il y a des erreurs)

---

## 🎯 Checklist Finale

- [ ] Logs Laravel ouverts (`tail -f storage/logs/laravel.log`)
- [ ] Test fait via WhatsApp
- [ ] Logs montrent "=== DÉBUT savePronostic ===" → API appelée ✅
- [ ] OU aucun log → API PAS appelée ❌
- [ ] Twilio Debugger vérifié (pas d'erreur HTTP)
- [ ] Widget `http_save_prono` en POST
- [ ] URL correcte: `https://can-wabracongo.ywcdigital.com/api/can/pronostic`
- [ ] Variables bien définies (test avec widget debug)
- [ ] Flow publié après modifications

---

## 💡 Astuce

**Le plus simple pour identifier le problème:**

1. **Ouvrir les logs** Laravel
2. **Faire un test** via WhatsApp
3. **Si les logs montrent l'appel** → Le problème est dans le message de retour
4. **Si aucun log** → Le problème est dans le widget Twilio

Avec cette info, je pourrai vous dire exactement quoi corriger ! 🎯
