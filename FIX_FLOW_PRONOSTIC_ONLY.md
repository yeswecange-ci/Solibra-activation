# Correction du Flow "Pronostic Uniquement"

## 🔴 Problème Identifié

Lorsque vous testez le flow de pronostic, vous recevez le message : **"❌ Erreur lors de l'enregistrement"**

### Cause Racine

Le widget `http_save_prono` essaie d'envoyer cette requête JSON :
```json
{
  "phone": "{{flow.variables.phone_number}}",
  "match_id": 1,
  "prediction_type": "team_a_win"
}
```

**MAIS** : La variable `flow.variables.phone_number` n'est **JAMAIS définie** dans le flow !

### Flux Actuel (Problématique)

```
Trigger
  ↓
http_get_matchs  ❌ phone_number n'est pas défini
  ↓
... (reste du flow)
  ↓
http_save_prono  ❌ Utilise phone_number qui est vide/undefined
```

Résultat : L'API reçoit un JSON invalide et retourne une erreur de validation.

## ✅ Solution

Ajouter un widget `set-variables` juste après le `Trigger` pour capturer le numéro de téléphone.

### Nouveau Flux (Corrigé)

```
Trigger
  ↓
set_phone  ✅ Capture trigger.message.From → phone_number
  ↓
http_get_matchs
  ↓
... (reste du flow)
  ↓
http_save_prono  ✅ Utilise phone_number qui contient le numéro réel
```

### Widget Ajouté

```json
{
  "name": "set_phone",
  "type": "set-variables",
  "transitions": [
    {
      "next": "http_get_matchs",
      "event": "next"
    }
  ],
  "properties": {
    "variables": [
      {
        "type": "string",
        "value": "{{trigger.message.From}}",
        "key": "phone_number"
      }
    ],
    "offset": {
      "x": 0,
      "y": 150
    }
  }
}
```

### Modification dans Trigger

**AVANT :**
```json
{
  "next": "http_get_matchs",
  "event": "incomingParent"
}
```

**APRÈS :**
```json
{
  "next": "set_phone",
  "event": "incomingParent"
}
```

## 📝 Autres Corrections

### Message d'Erreur Amélioré

**AVANT :**
```
"body": "❌ Erreur lors de l'enregistrement. {{widgets.http_save_prono.parsed.message}}"
```

**APRÈS :**
```
"body": "❌ Erreur: {{widgets.http_save_prono.parsed.message}}"
```

Cela affichera le message d'erreur exact retourné par l'API, ce qui facilitera le debugging.

## 🧪 Comment Tester

### Avant de Déployer

Vérifiez que votre fichier JSON contient :

1. **Le widget set_phone** entre Trigger et http_get_matchs
2. **La transition mise à jour** dans Trigger vers set_phone

### Après le Déploiement

1. Envoyez un message WhatsApp à votre numéro Twilio
2. Choisissez un match (ex: 1)
3. Choisissez un pronostic (ex: 1 pour victoire équipe A)
4. Vous devriez recevoir : "✅ Pronostic enregistré ! ..."

### Si Ça Ne Marche Toujours Pas

Vérifiez dans les logs Twilio Studio :
1. Allez dans Twilio Console → Studio → votre Flow
2. Cliquez sur "Execution Logs"
3. Regardez la valeur de `{{flow.variables.phone_number}}`
   - ✅ Devrait être : `whatsapp:+243828500007`
   - ❌ Si vide ou undefined : le widget set_phone n'est pas exécuté

## 📊 Comparaison

| Élément | Flow Problématique | Flow Corrigé |
|---------|-------------------|--------------|
| **Widget set_phone** | ❌ Absent | ✅ Présent |
| **phone_number** | ❌ Undefined | ✅ Défini |
| **Requête API** | ❌ Échoue (validation) | ✅ Réussit |
| **Message reçu** | "❌ Erreur lors de l'enregistrement" | "✅ Pronostic enregistré !" |

## 🚀 Déploiement

### Option 1 : Import JSON

1. Ouvrez Twilio Studio Console
2. Allez dans votre flow "CAN 2025 - Flow Pronostics Uniquement"
3. Cliquez sur les **trois points** → **Import from JSON**
4. Collez le contenu de `twilio_flow_pronostic_only_FIXED.json`
5. Cliquez sur **Save**
6. Cliquez sur **Publish**

### Option 2 : Modification Manuelle

1. Ouvrez votre flow dans Twilio Studio
2. Ajoutez un nouveau widget **Set Variables** après le Trigger
   - Name: `set_phone`
   - Variable 1:
     - Key: `phone_number`
     - Value: `{{trigger.message.From}}`
3. Reconnectez Trigger → set_phone → http_get_matchs
4. Save & Publish

## ⚠️ Important

Si vous avez plusieurs flows (inscription + pronostic), assurez-vous que :
- **Flow d'inscription complet** : Capture phone_number dans `set_phone`
- **Flow de pronostic uniquement** : Capture AUSSI phone_number dans `set_phone`

Les deux flows ont besoin de capturer le numéro de téléphone au début !

## ✅ Vérification Finale

Après le déploiement, testez avec un vrai message WhatsApp et vérifiez que :

1. ✅ Vous recevez la liste des matchs
2. ✅ Vous pouvez choisir un match
3. ✅ Vous pouvez choisir un pronostic
4. ✅ Vous recevez "Pronostic enregistré !" avec le message de l'API
5. ✅ Le pronostic apparaît dans `/admin/pronostics`

---

**Fichier corrigé disponible :** `twilio_flow_pronostic_only_FIXED.json`
