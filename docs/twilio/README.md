# Flow Twilio Studio - FlowSimpleSocialV2 (Mis à jour)

## Changements effectués

### 1. **Ajout du champ `quiz_answer` dans la base de données**
   - Migration créée : `2025_12_25_192228_add_quiz_answer_to_users_table.php`
   - Champ ajouté après `boisson_preferee` dans la table `users`
   - Modèle `User` mis à jour pour inclure `quiz_answer` dans `$fillable`

### 2. **Nouvel endpoint `/api/can/inscription-simple`**
   - Endpoint créé spécialement pour ce flow Twilio Studio
   - Accepte les paramètres suivants :
     - `phone` (requis) : Numéro de téléphone de l'utilisateur
     - `answer_1` (requis) : Boisson préférée
     - `answer_2` (requis) : Réponse au quiz ("OUI" ou "NON")
     - `status` (optionnel) : Statut de l'inscription
     - `timestamp` (optionnel) : Horodatage

   - Fonctionnalités :
     - Crée un nouvel utilisateur avec un nom générique (`Participant_XXXX`)
     - Met à jour un utilisateur existant s'il existe déjà
     - Sauvegarde `answer_1` dans `boisson_preferee`
     - Sauvegarde `answer_2` dans `quiz_answer`
     - Assigne automatiquement le premier village actif
     - Marque le `source_type` comme `WHATSAPP_FLOW`

### 3. **URLs mises à jour dans le flow**

Toutes les URLs ont été mises à jour de :
```
https://votre-api-solibra.com/api/solibra/*
```

Vers :
```
http://localhost/api/can/*
```

#### Endpoints utilisés dans le flow :

| Action | Endpoint | Méthode | Body |
|--------|----------|---------|------|
| Vérifier utilisateur | `/api/can/check-user` | POST | `{phone}` |
| Inscription | `/api/can/inscription-simple` | POST | `{phone, answer_1, answer_2, status, timestamp}` |
| Réactivation | `/api/can/reactivate` | POST | `{phone, status, timestamp}` |
| Refus | `/api/can/refus` | POST | `{phone, status, timestamp}` |
| Stop | `/api/can/stop` | POST | `{phone, status, timestamp}` |
| Abandon | `/api/can/abandon` | POST | `{phone, status, timestamp}` |
| Timeout | `/api/can/timeout` | POST | `{phone, status, timestamp}` |
| Erreur | `/api/can/error` | POST | `{phone, status, timestamp}` |

## Configuration pour la production

### Étape 1 : Changer l'URL de base

Le flow utilise actuellement `http://localhost` comme URL de base. Pour la production, vous devez :

1. Ouvrir le fichier `flow_simple_social_v2_updated.json`
2. Faire un **Chercher/Remplacer global** :
   - Chercher : `http://localhost`
   - Remplacer par : `https://votre-domaine.com`

Exemple avec VS Code :
```bash
# Linux/Mac
sed -i 's|http://localhost|https://votre-domaine.com|g' flow_simple_social_v2_updated.json

# Windows PowerShell
(Get-Content flow_simple_social_v2_updated.json) -replace 'http://localhost', 'https://votre-domaine.com' | Set-Content flow_simple_social_v2_updated.json
```

### Étape 2 : Importer dans Twilio Studio

1. Connectez-vous à votre console Twilio
2. Allez dans **Studio > Flows**
3. Créez un nouveau Flow ou éditez le flow existant
4. Cliquez sur **Import from JSON**
5. Collez le contenu de `flow_simple_social_v2_updated.json`
6. Publiez le flow

### Étape 3 : Tester le flow

Vous pouvez tester les endpoints directement avec Postman ou cURL :

#### Test 1 : Check User
```bash
curl -X POST http://localhost/api/can/check-user \
  -H "Content-Type: application/json" \
  -d '{"phone": "+2250757123456"}'
```

**Réponses attendues :**
- Utilisateur non trouvé : `{"status": "NOT_FOUND", "message": "User not found"}`
- Utilisateur inscrit : `{"status": "INSCRIT", "name": "...", "phone": "...", "user_id": ...}`
- Utilisateur stopped : `{"status": "STOP", "name": "...", "phone": "..."}`

#### Test 2 : Inscription Simple
```bash
curl -X POST http://localhost/api/can/inscription-simple \
  -H "Content-Type: application/json" \
  -d '{
    "phone": "+2250757123456",
    "answer_1": "BOCK",
    "answer_2": "OUI",
    "status": "INSCRIT",
    "timestamp": "2025-12-25 19:30:00"
  }'
```

**Réponse attendue :**
```json
{
  "success": true,
  "message": "User registered successfully",
  "user_id": 1,
  "name": "Participant_3456"
}
```

## Structure du flow

### Flow principal

1. **Trigger** → Reçoit le message WhatsApp
2. **set_phone** → Extrait le numéro de téléphone et le message
3. **check_stop_keyword** → Vérifie si l'utilisateur demande STOP
4. **http_check_user** → Appel API pour vérifier si l'utilisateur existe
5. **check_user_status** → Route selon le statut :
   - `NOT_FOUND` → Nouveau participant → Continue vers questions
   - `INSCRIT` → Déjà inscrit → Message de confirmation
   - `STOP` → Ancien participant → Proposition de réactivation

### Questions et validation

6. **msg_question_1** → Question 1 : Boisson préférée
7. **run_function_validate_drink** → Validation de la boisson (Twilio Function)
8. **msg_question_2** → Question 2 : Quiz BOCK/WORLD COLA partenaires FIF
9. **set_answer_2** → Sauvegarde de la réponse
10. **msg_pdf_validation** → Envoi du PDF et demande de validation
11. **http_save_inscription** → **Appel API `/api/can/inscription-simple`**
12. **msg_confirmation_finale** → Message de félicitations

### Gestion des erreurs

- **http_log_timeout** → Log des timeouts
- **http_log_error** → Log des erreurs de livraison
- **http_log_abandon** → Log des abandons
- **http_log_refus** → Log des refus

## Variables flow

Le flow utilise les variables suivantes :

| Variable | Description | Exemple |
|----------|-------------|---------|
| `phone_number` | Numéro de téléphone WhatsApp | `whatsapp:+2250757123456` |
| `user_message` | Message envoyé par l'utilisateur | `BOCK` |
| `timestamp` | Horodatage de l'interaction | `2025-12-25 19:30:00` |
| `last_drink_input` | Dernière saisie boisson | `BOCK` |
| `answer_1` | Boisson préférée validée | `BOCK` |
| `answer_2` | Réponse au quiz | `OUI` |
| `flow_status` | Statut final du flow | `INSCRIPTION_COMPLETE` |

## Base de données

### Table `users` - Nouveaux champs

```sql
ALTER TABLE users ADD COLUMN quiz_answer VARCHAR(255) NULL AFTER boisson_preferee;
```

### Données sauvegardées lors de l'inscription

```php
[
    'phone' => '+2250757123456',
    'name' => 'Participant_3456',  // Généré automatiquement
    'boisson_preferee' => 'BOCK',  // answer_1
    'quiz_answer' => 'OUI',        // answer_2
    'village_id' => 1,             // Premier village actif
    'source_type' => 'WHATSAPP_FLOW',
    'source_detail' => 'FlowSimpleSocialV2',
    'registration_status' => 'INSCRIT',
    'opted_in_at' => '2025-12-25 19:30:00',
    'is_active' => true,
]
```

## Notes importantes

1. **Validation de la boisson** : Le flow utilise une Twilio Function (`validate_solibra_drink`) qui doit être déployée séparément
2. **PDF de confidentialité** : L'URL du PDF doit être mise à jour dans le state `msg_pdf_validation`
3. **Image de bienvenue** : L'URL de l'image doit être mise à jour dans le state `send_message_1`
4. **Village par défaut** : L'endpoint utilise le premier village actif. Assurez-vous d'avoir au moins un village actif dans la base de données
5. **Format du téléphone** : Le controller formate automatiquement les numéros (ajoute `+` si nécessaire)

## Maintenance

Pour voir les logs des appels API :

```bash
tail -f storage/logs/laravel.log | grep "Twilio Studio"
```

Pour vérifier les utilisateurs inscrits via ce flow :

```php
User::where('source_type', 'WHATSAPP_FLOW')
    ->where('source_detail', 'FlowSimpleSocialV2')
    ->get();
```

## Support

Pour toute question ou problème, vérifiez :
1. Les logs Laravel : `storage/logs/laravel.log`
2. Les logs Twilio Studio dans la console Twilio
3. La console de débogage Twilio : https://www.twilio.com/console/runtime/debugger
