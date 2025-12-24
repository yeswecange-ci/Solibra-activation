# 🚀 Guide de Migration Rapide - Flow Optimisé

## ⚡ Démarrage Rapide (15 minutes)

### Étape 1 : Vérifier les prérequis ✅

**Backend Laravel - Endpoints API requis** :

```bash
# Tester que ces endpoints existent et répondent correctement
curl -X POST https://app-can-solibra.ywcdigital.com/api/can/check-user \
  -H "Content-Type: application/json" \
  -d '{"phone":"whatsapp:+243999999999"}'

# Nouveaux endpoints à créer si manquants :
# - POST /api/can/abandon
# - POST /api/can/timeout
# - POST /api/can/error (avec support du champ "error")
```

**Checklist Backend** :
- [ ] Endpoint `/api/can/check-user` existe
- [ ] Endpoint `/api/can/scan` existe
- [ ] Endpoint `/api/can/optin` existe
- [ ] Endpoint `/api/can/inscription` existe
- [ ] Endpoint `/api/can/refus` existe
- [ ] Endpoint `/api/can/stop` existe
- [ ] Endpoint `/api/can/reactivate` existe
- [ ] Endpoint `/api/can/abandon` existe ⭐ NOUVEAU
- [ ] Endpoint `/api/can/timeout` existe ⭐ NOUVEAU
- [ ] Endpoint `/api/can/error` existe ⭐ NOUVEAU

---

### Étape 2 : Créer les endpoints manquants (si nécessaire) 🔧

Si vous n'avez pas les nouveaux endpoints, ajoutez-les à votre contrôleur :

**Fichier** : `app/Http/Controllers/Api/TwilioStudioController.php`

```php
/**
 * Log abandon (trop de tentatives invalides)
 */
public function abandon(Request $request)
{
    $validated = $request->validate([
        'phone' => 'required|string',
        'timestamp' => 'nullable|string',
    ]);

    Log::info('📛 ABANDON', [
        'phone' => $validated['phone'],
        'timestamp' => $validated['timestamp'] ?? now(),
    ]);

    // Optionnel : Mettre à jour le statut dans la base
    $user = User::where('phone', $validated['phone'])->first();
    if ($user) {
        $user->update(['registration_status' => 'ABANDON']);
    }

    return response()->json([
        'success' => true,
        'message' => 'Abandon enregistré',
    ]);
}

/**
 * Log timeout (pas de réponse)
 */
public function timeout(Request $request)
{
    $validated = $request->validate([
        'phone' => 'required|string',
        'timestamp' => 'nullable|string',
    ]);

    Log::info('⏱️ TIMEOUT', [
        'phone' => $validated['phone'],
        'timestamp' => $validated['timestamp'] ?? now(),
    ]);

    return response()->json([
        'success' => true,
        'message' => 'Timeout enregistré',
    ]);
}

/**
 * Log erreurs techniques
 */
public function error(Request $request)
{
    $validated = $request->validate([
        'phone' => 'required|string',
        'status' => 'nullable|string',
        'error' => 'nullable|string',
        'timestamp' => 'nullable|string',
    ]);

    Log::error('❌ ERREUR TECHNIQUE', [
        'phone' => $validated['phone'],
        'status' => $validated['status'] ?? 'ERROR',
        'error' => $validated['error'] ?? 'Unknown error',
        'timestamp' => $validated['timestamp'] ?? now(),
    ]);

    return response()->json([
        'success' => true,
        'message' => 'Erreur enregistrée',
    ]);
}
```

**Ajouter les routes** dans `routes/api.php` :

```php
// Dans le groupe /api/can/
Route::post('/abandon', [TwilioStudioController::class, 'abandon']);
Route::post('/timeout', [TwilioStudioController::class, 'timeout']);
Route::post('/error', [TwilioStudioController::class, 'error']);
```

**Tester** :
```bash
php artisan route:list | grep abandon
php artisan route:list | grep timeout
php artisan route:list | grep error
```

---

### Étape 3 : Récupérer les SIDs des subflows 🔑

Vous devez connaître les SIDs Twilio de vos subflows existants :

1. **Aller dans Twilio Console** → Studio → Flows
2. **Noter les SIDs** de chaque subflow :

| Subflow | SID Actuel | Variable dans le JSON |
|---------|------------|----------------------|
| Villages/Matchs | `FWf255f47348477f7b361f4b7df59d5fd5` | `subflow_matchs` |
| Pronostic | `FW26cc752ab63630c73404fab72632f65c` | `subflow_pronostic` |
| Mes Pronostics | **À REMPLACER** | `subflow_mes_pronos` |
| Classement | **À REMPLACER** | `subflow_classement` |
| Quiz | `FW6643799ed631c2c6a966923e94e11cce` | `subflow_quiz` |

**Si vous n'avez pas créé "Mes Pronostics" et "Classement"** :
- Option A : Les créer maintenant
- Option B : Utiliser temporairement un autre subflow existant
- Option C : Supprimer ces états du flow pour l'instant

---

### Étape 4 : Modifier le fichier JSON avec vos SIDs 📝

Ouvrir le fichier :
```
C:\wamp64\www\YESWECANGE\Solibra-activation\twilio_studio_flow_OPTIMIZED.json
```

**Rechercher et remplacer** :

1. **Subflow Mes Pronostics** (ligne ~1420) :
   ```json
   "flow_sid": "FW_MES_PRONOS_SID_HERE"
   ```
   Remplacer par :
   ```json
   "flow_sid": "VOTRE_VRAI_SID_ICI"
   ```

2. **Subflow Classement** (ligne ~1445) :
   ```json
   "flow_sid": "FW_CLASSEMENT_SID_HERE"
   ```
   Remplacer par :
   ```json
   "flow_sid": "VOTRE_VRAI_SID_ICI"
   ```

3. **Vérifier les autres SIDs** sont corrects :
   - Matchs : `FWf255f47348477f7b361f4b7df59d5fd5`
   - Pronostic : `FW26cc752ab63630c73404fab72632f65c`
   - Quiz : `FW6643799ed631c2c6a966923e94e11cce`

**Sauvegarder** le fichier après modifications.

---

### Étape 5 : Importer le flow dans Twilio Studio 📥

1. **Aller dans Twilio Console** → Studio → Create new Flow
2. **Nom du flow** : `Solibra CAN 2025 - Optimisé v2`
3. **Choose starting point** : Import from JSON
4. **Copier/coller** le contenu de `twilio_studio_flow_OPTIMIZED.json` (modifié)
5. **Cliquer** "Next"
6. **Vérifier** qu'il n'y a pas d'erreurs
7. **Publier** le flow (Save & Publish)

**En cas d'erreur** :
- Vérifier que tous les SIDs sont valides
- Vérifier que le JSON est bien formaté (pas de virgules manquantes)
- Vérifier que les subflows existent

---

### Étape 6 : Configuration du numéro WhatsApp 📞

**Option A : Test en parallèle (recommandé)**
1. Garder l'ancien flow sur le numéro principal
2. Configurer un numéro de test avec le nouveau flow
3. Tester tous les scénarios
4. Switcher quand tout est OK

**Option B : Remplacement direct**
1. Aller dans Twilio Console → Messaging → WhatsApp Senders
2. Cliquer sur votre numéro
3. Section "Configure" → "When a message comes in"
4. Sélectionner le nouveau flow : `Solibra CAN 2025 - Optimisé v2`
5. **Sauvegarder**

---

### Étape 7 : Tests Essentiels (30 minutes) 🧪

**Tests minimums avant mise en production** :

#### Test 1 : Nouvelle inscription
```
Envoyer : START_AFF_GOMBE
Répondre : OUI
Répondre : TestUser
Résultat attendu : Inscription + Menu affiché
```

#### Test 2 : Utilisateur déjà inscrit
```
Envoyer : MENU
Résultat attendu : Menu affiché directement
```

#### Test 3 : Routage pronostic
```
Envoyer : PRONOSTIC (ou 2)
Résultat attendu : Subflow pronostic lancé
```

#### Test 4 : Gestion d'erreur
```
Envoyer : START_AFF_GOMBE
Répondre : BLABLA
Répondre : NIMPORTEQUOI
Résultat attendu : Message d'abandon après 2 tentatives
```

#### Test 5 : STOP
```
Envoyer : STOP
Résultat attendu : Message de désabonnement
```

#### Test 6 : Réactivation
```
(Après STOP)
Envoyer : Bonjour
Répondre : OUI
Résultat attendu : Réactivation + Menu
```

**Checklist** :
- [ ] Inscription fonctionne
- [ ] Menu s'affiche pour utilisateurs inscrits
- [ ] Routage vers subflows fonctionne
- [ ] Gestion d'erreurs fonctionne (max 2 tentatives)
- [ ] STOP fonctionne
- [ ] Réactivation fonctionne
- [ ] Logs API corrects

---

### Étape 8 : Vérifier les logs 📊

**Logs Laravel** :
```bash
tail -f storage/logs/laravel.log
```

**Vérifier que ces événements sont loggés** :
- ✅ SCAN
- ✅ OPTIN
- ✅ INSCRIPTION
- ✅ REFUS
- ✅ STOP
- ✅ ABANDON ⭐ NOUVEAU
- ✅ TIMEOUT ⭐ NOUVEAU
- ✅ ERREUR TECHNIQUE ⭐ NOUVEAU

**Logs Twilio Studio** :
1. Aller dans le flow
2. Cliquer "View Executions"
3. Vérifier les transitions
4. Vérifier les variables

---

## 🎯 Déploiement en Production

### Phase 1 : Tests Internes (Jour 1-2)
- [ ] Tester avec 3-5 numéros de l'équipe
- [ ] Valider tous les scénarios (voir FLOW_TEST_SCENARIOS.md)
- [ ] Vérifier les logs
- [ ] Corriger les bugs éventuels

### Phase 2 : Beta Testing (Jour 3-5)
- [ ] 10% du trafic sur le nouveau flow
- [ ] Monitoring actif
- [ ] Collecte feedback utilisateurs
- [ ] Analyse des métriques

### Phase 3 : Rollout Complet (Jour 6+)
- [ ] Si tout OK : 100% du trafic
- [ ] Monitoring continu 48h
- [ ] Désactiver l'ancien flow après 7 jours
- [ ] Documentation mise à jour

---

## 🔍 Monitoring Post-Migration

### Métriques à Surveiller

**Dashboard Laravel** (à créer si nécessaire) :
```sql
-- Taux d'abandon
SELECT COUNT(*) FROM users WHERE registration_status = 'ABANDON';

-- Taux de complétion
SELECT
  COUNT(CASE WHEN registration_status = 'INSCRIT' THEN 1 END) * 100.0 / COUNT(*) as taux_completion
FROM users
WHERE created_at > NOW() - INTERVAL 7 DAY;

-- Erreurs API
SELECT COUNT(*) FROM message_logs WHERE status = 'ERROR' AND created_at > NOW() - INTERVAL 1 DAY;

-- Timeouts
SELECT COUNT(*) FROM message_logs WHERE status = 'TIMEOUT' AND created_at > NOW() - INTERVAL 1 DAY;
```

**Twilio Studio Analytics** :
- Flow Executions (nombre total)
- Completion Rate
- Average Duration
- Error Rate

---

## ⚠️ Rollback Plan

**Si quelque chose ne va pas** :

### Option 1 : Rollback immédiat (5 minutes)
1. Aller dans Twilio Console → Messaging
2. Configurer le numéro WhatsApp avec l'ancien flow
3. **Sauvegarder**

### Option 2 : Restaurer une version précédente
1. Aller dans Studio → Votre flow
2. Cliquer "Revision History"
3. Sélectionner une version antérieure
4. Publish

### Option 3 : Désactiver temporairement
1. Configurer le numéro pour ne pas utiliser de flow
2. Afficher un message de maintenance
3. Réparer le problème
4. Re-activer

---

## 📚 Documentation de Référence

Après migration, conservez ces fichiers :

1. **twilio_studio_flow_OPTIMIZED.json** - Flow complet
2. **FLOW_OPTIMIZED_DOCUMENTATION.md** - Doc détaillée
3. **FLOW_TEST_SCENARIOS.md** - 35 scénarios de test
4. **FLOW_COMPARISON_OLD_VS_NEW.md** - Comparaison
5. **MIGRATION_GUIDE_QUICK_START.md** - Ce guide
6. **twilio_studio_flow_PRODUCTION.json** - Backup ancien flow

---

## ✅ Checklist Finale de Migration

### Avant le Déploiement
- [ ] Endpoints API créés et testés
- [ ] SIDs des subflows mis à jour dans le JSON
- [ ] Flow importé dans Twilio Studio
- [ ] Flow publié sans erreurs
- [ ] Tests essentiels passés
- [ ] Logs vérifiés
- [ ] Backup de l'ancien flow créé
- [ ] Plan de rollback documenté

### Pendant le Déploiement
- [ ] Numéro configuré avec nouveau flow
- [ ] Monitoring actif
- [ ] Équipe disponible pour support
- [ ] Tests en conditions réelles

### Après le Déploiement
- [ ] Métriques surveillées (24-48h)
- [ ] Bugs corrigés rapidement
- [ ] Feedback utilisateurs collecté
- [ ] Documentation mise à jour
- [ ] Équipe formée sur nouveau flow

---

## 🆘 Besoin d'Aide ?

### Problèmes Courants

**Problème** : "Error importing JSON"
**Solution** : Vérifier que tous les SIDs sont valides et que le JSON est bien formaté

**Problème** : "Subflow not found"
**Solution** : Vérifier que le SID du subflow existe et est publié

**Problème** : "API timeout"
**Solution** : Augmenter le timeout à 15000ms au lieu de 10000ms

**Problème** : "User stuck in loop"
**Solution** : Vérifier que retry_count est bien reset après chaque étape

**Problème** : "STOP not detected"
**Solution** : Vérifier que la normalisation fonctionne (upcase, strip)

### Contacts Support
- **Logs Laravel** : `tail -f storage/logs/laravel.log`
- **Twilio Support** : https://console.twilio.com/support
- **Documentation** : Voir fichiers MD dans le projet

---

## 🎉 Félicitations !

Si vous avez suivi toutes les étapes, votre flow optimisé est maintenant en production avec :

✅ Gestion d'erreurs robuste
✅ Routage intelligent
✅ Messages clairs
✅ Logging exhaustif
✅ Expérience utilisateur améliorée

**Prochaines étapes** :
1. Monitorer les métriques pendant 1 semaine
2. Analyser les retours utilisateurs
3. Optimiser les messages si nécessaire
4. Créer les subflows manquants (Mes Pronos, Classement)

---

**Date de création** : 2025-01-15
**Version** : 1.0
**Auteur** : Claude Code
**Temps estimé** : 45 minutes (15 min config + 30 min tests)
