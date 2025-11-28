# 📲 Guide d'Import du Flow Twilio Studio - PRODUCTION

## ✅ URLs Mises à Jour

Le flow a été mis à jour avec les bonnes URLs de production :

| Endpoint | URL Complète | État |
|----------|-------------|------|
| `/api/can/scan` | `https://wabracongo.ywcdigital.com/api/can/scan` | ✅ |
| `/api/can/optin` | `https://wabracongo.ywcdigital.com/api/can/optin` | ✅ |
| `/api/can/inscription` | `https://wabracongo.ywcdigital.com/api/can/inscription` | ✅ |
| `/api/can/refus` | `https://wabracongo.ywcdigital.com/api/can/refus` | ✅ |
| `/api/can/stop` | `https://wabracongo.ywcdigital.com/api/can/stop` | ✅ |
| `/api/can/abandon` | `https://wabracongo.ywcdigital.com/api/can/abandon` | ✅ |
| `/api/can/timeout` | `https://wabracongo.ywcdigital.com/api/can/timeout` | ✅ |
| `/api/can/error` | `https://wabracongo.ywcdigital.com/api/can/error` | ✅ |

---

## 📋 Étapes d'Import dans Twilio Studio

### Option 1 : Créer un Nouveau Flow (Recommandé)

**1. Connexion à Twilio Console**
- Va sur https://console.twilio.com
- Connecte-toi avec ton compte

**2. Accéder à Twilio Studio**
- Dans le menu de gauche : **Develop** → **Studio** → **Flows**
- Ou directement : https://console.twilio.com/us1/develop/studio/flows

**3. Créer un Nouveau Flow**
- Clique sur **Create new Flow**
- Nom : `CAN 2025 Kinshasa - Production`
- Type : **Start from scratch**
- Clique sur **Next**

**4. Importer le Flow JSON**
- Dans l'éditeur de flow qui s'ouvre, clique sur les **3 points** (⋮) en haut à droite
- Sélectionne **Import from JSON**
- Copie le contenu du fichier `twilio_studio_flow_PRODUCTION.json`
- Colle-le dans la fenêtre d'import
- Clique sur **Import**

**5. Vérifier le Flow**
- Vérifie que toutes les étapes sont bien connectées
- Vérifie surtout les blocs HTTP Request (doivent contenir `wabracongo.ywcdigital.com`)
- Clique sur **Publish** pour activer le flow

---

### Option 2 : Mettre à Jour un Flow Existant

**1. Ouvrir ton Flow Existant**
- Dans Twilio Studio → Flows
- Clique sur ton flow actuel

**2. Mettre à jour les URLs**
Pour chaque bloc HTTP Request, remplace l'URL :

**❌ Avant :**
```
https://VOTRE-SERVEUR.com/api/can/scan
```

**✅ Après :**
```
https://wabracongo.ywcdigital.com/api/can/scan
```

**Blocs à mettre à jour (8 au total) :**

1. **`http_log_scan`**
   - URL : `https://wabracongo.ywcdigital.com/api/can/scan`

2. **`http_log_scan_direct`**
   - URL : `https://wabracongo.ywcdigital.com/api/can/scan`

3. **`http_log_optin`**
   - URL : `https://wabracongo.ywcdigital.com/api/can/optin`

4. **`http_log_inscription`**
   - URL : `https://wabracongo.ywcdigital.com/api/can/inscription`

5. **`http_log_refus`**
   - URL : `https://wabracongo.ywcdigital.com/api/can/refus`

6. **`http_log_stop`**
   - URL : `https://wabracongo.ywcdigital.com/api/can/stop`

7. **`http_log_abandon`**
   - URL : `https://wabracongo.ywcdigital.com/api/can/abandon`

8. **`timeout_accueil`, `timeout_relance`, `timeout_nom`** (3 blocs)
   - URL : `https://wabracongo.ywcdigital.com/api/can/timeout`

9. **`delivery_failed`**
   - URL : `https://wabracongo.ywcdigital.com/api/can/error`

**3. Sauvegarder**
- Clique sur **Publish** pour activer les modifications

---

## 🧪 Tester le Flow

### Test 1 : Via Twilio Console (Simulation)

**1. Dans l'éditeur du flow :**
- Clique sur le bouton **▶ Test** en haut à droite
- Dans "Incoming Message Body", entre : `START_AFF_GOMBE`
- Dans "From", entre un numéro test : `whatsapp:+243812345678`
- Clique sur **Run**

**2. Vérifier les logs :**
- Vérifie que chaque étape HTTP Request montre `200 OK`
- Regarde les réponses JSON des APIs

**3. Dans ta base de données :**
```bash
# Depuis Coolify Terminal
php artisan tinker
>>> \App\Models\User::latest()->first()
```

Tu devrais voir un nouvel utilisateur avec :
- `phone` : `whatsapp:+243812345678`
- `source_type` : `AFFICHE`
- `source_detail` : `GOMBE`

---

### Test 2 : Via WhatsApp (Réel)

**1. Configure ton numéro WhatsApp Twilio :**
- Va dans **Messaging** → **Services** → **WhatsApp senders**
- Sélectionne ton numéro WhatsApp
- Dans **Incoming messages**, configure :
  - **Configuration type** : Twilio Studio Flow
  - **Flow** : Sélectionne `CAN 2025 Kinshasa - Production`
- Sauvegarde

**2. Envoie un message WhatsApp de test :**
- Depuis ton téléphone, envoie à ton numéro Twilio :
  ```
  START_AFF_GOMBE
  ```

**3. Le bot devrait répondre :**
```
🦁 BIENVENUE !

La CAN arrive à Kinshasa !

🎁 Inscris-toi pour :
→ Gagner des cadeaux
→ Rejoindre un Village CAN
→ Participer aux jeux

Partenaires : Bracongo, Vodacom, Orange

👉 Tape OUI pour t'inscrire
```

**4. Continue le flow :**
- Tape **OUI**
- Donne un nom : **Test**
- Tu devrais recevoir le message de confirmation

**5. Vérifie dans la base de données :**
```bash
php artisan tinker
>>> \App\Models\User::latest()->first()
>>> \App\Models\ConversationSession::latest()->first()
```

---

## 📊 Monitoring du Flow

### 1. Logs Twilio Studio

- Va dans **Studio** → **Flows** → Ton flow
- Clique sur **Logs** dans le menu
- Tu verras tous les executions avec détails

### 2. Logs Laravel

```bash
# Depuis Coolify Terminal
tail -f /var/www/html/storage/logs/laravel.log
```

Tu verras toutes les requêtes API entrantes :
```
[2025-11-28 12:00:00] local.INFO: API Scan: {"phone":"whatsapp:+243812345678","source_type":"AFFICHE"}
```

### 3. Dashboard Admin

- Connexion : https://wabracongo.ywcdigital.com/admin/login
- Dashboard : Les stats se mettent à jour en temps réel

---

## 🔧 Configuration Avancée

### Ajouter des Variables d'Environnement Twilio

Si tu veux rendre l'URL configurable :

**1. Dans Twilio Studio Flow :**
- Ajoute un widget `Set Variables` au début
- Variable : `api_base_url`
- Valeur : `https://wabracongo.ywcdigital.com`

**2. Dans les widgets HTTP Request :**
- Remplace l'URL par : `{{flow.variables.api_base_url}}/api/can/scan`

**Avantage :** Tu n'as qu'à changer une variable pour basculer entre dev et prod.

---

## 🎯 QR Codes à Créer

Pour que les utilisateurs puissent scanner et démarrer le flow, tu dois créer des QR Codes qui envoient les messages suivants :

### QR Codes Affiches (par Village)
```
START_AFF_GOMBE
START_AFF_MASINA
START_AFF_LEMBA
START_AFF_BANDA
START_AFF_NGALI
```

### QR Codes Points de Vente
```
START_PDV_BRACONGO
START_PDV_VODACOM
START_PDV_ORANGE
START_PDV_AIRTEL
```

### QR Codes Digital
```
START_FB
START_IG
START_TIKTOK
START_WA_STATUS
```

### QR Codes Flyers
```
START_FLYER_UNI
START_FLYER_RUE
START_FLYER_EVENT
```

**Outil pour créer les QR Codes WhatsApp :**
- https://wa.me/NUMERO_TWILIO?text=START_AFF_GOMBE
- Exemple : https://wa.me/14155238886?text=START_AFF_GOMBE

---

## ✅ Checklist Finale

Avant de lancer en production :

- [ ] ✅ Flow importé dans Twilio Studio
- [ ] ✅ Toutes les URLs pointent vers `wabracongo.ywcdigital.com`
- [ ] ✅ Flow publié (bouton "Publish" cliqué)
- [ ] ✅ Numéro WhatsApp configuré pour utiliser ce flow
- [ ] ✅ Test réussi via console Twilio
- [ ] ✅ Test réussi via WhatsApp réel
- [ ] ✅ Données enregistrées correctement en base
- [ ] ✅ Dashboard admin affiche les stats
- [ ] ✅ QR Codes créés pour chaque source
- [ ] ✅ Au moins 1 village actif dans la base de données

---

## 🐛 Troubleshooting

### Problème 1 : HTTP Request échoue avec 404

**Cause :** URL incorrecte ou route non configurée

**Vérification :**
```bash
# Tester l'API directement
curl -X POST https://wabracongo.ywcdigital.com/api/can/scan \
  -H "Content-Type: application/json" \
  -d '{"phone":"whatsapp:+243812345678","source_type":"AFFICHE","source_detail":"GOMBE","timestamp":"2025-11-28 12:00:00","status":"SCAN"}'
```

**Résultat attendu :**
```json
{"success":true,"message":"Scan logged successfully","session_id":1}
```

### Problème 2 : HTTP Request échoue avec 500

**Cause :** Erreur dans le code Laravel

**Vérification :**
```bash
# Voir les logs Laravel
tail -f /var/www/html/storage/logs/laravel.log
```

**Solutions courantes :**
- Village n'existe pas → Créer au moins 1 village actif
- Champs manquants → Vérifier le body JSON envoyé

### Problème 3 : Le flow ne se déclenche pas

**Vérifications :**
1. **Numéro WhatsApp configuré ?**
   - Messaging → Services → Incoming messages → Studio Flow sélectionné

2. **Flow publié ?**
   - Clique sur **Publish** dans l'éditeur

3. **Sandbox WhatsApp activé ?**
   - Pour tests : rejoindre le sandbox Twilio
   - Pour prod : numéro WhatsApp vérifié

---

## 📞 Support

**Logs à vérifier en cas de problème :**
1. Twilio Studio Logs : https://console.twilio.com/us1/develop/studio/flows
2. Laravel Logs : `tail -f storage/logs/laravel.log`
3. Coolify Logs : Dans l'interface Coolify

**Commandes utiles :**
```bash
# Voir les derniers utilisateurs créés
php artisan tinker
>>> \App\Models\User::latest()->take(5)->get()

# Voir les dernières sessions
>>> \App\Models\ConversationSession::latest()->take(5)->get()

# Compter les inscriptions
>>> \App\Models\User::where('is_active', true)->count()
```

---

**Le flow est maintenant prêt pour la production ! 🚀**
