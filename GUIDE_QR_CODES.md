# 📱 Guide Complet - QR Codes WhatsApp

## ✅ Problème Résolu

**Avant :**
- Scanner le QR code demandait de choisir un contact
- Le message n'était pas au bon format

**Maintenant :**
- ✅ Scan du QR code ouvre directement WhatsApp avec le bot (+243841622222)
- ✅ Message pré-rempli au bon format (ex: `START_AFF_GOMBE`)
- ✅ L'utilisateur n'a qu'à appuyer sur "Envoyer"

---

## 🎯 Comment ça fonctionne

### 1. Flux Utilisateur

```
📱 Utilisateur scanne le QR code
  ↓
🌐 Redirection vers /qr/{CODE_UNIQUE}
  ↓
📊 Compteur de scan incrémenté
  ↓
💬 Redirection vers WhatsApp
  ↓
✅ Conversation ouverte avec le bot (+243841622222)
  ↓
📝 Message pré-rempli : "START_AFF_GOMBE"
  ↓
👆 Utilisateur appuie sur "Envoyer"
  ↓
🤖 Bot Twilio Studio répond automatiquement
```

---

## 🏗️ Créer des QR Codes

### Via l'Admin

1. **Connexion :** https://wabracongo.ywcdigital.com/admin/qrcodes

2. **Créer un QR Code** :
   - Clique sur "Nouveau QR Code"
   - Entre la **source** (ex: `GOMBE`, `BRACONGO`, `FACEBOOK`)
   - Coche "Actif"
   - Clique sur "Créer"

3. **Télécharger** :
   - Le QR code est généré automatiquement
   - Clique sur "Télécharger" pour obtenir l'image PNG

---

## 📋 Sources Disponibles

Le système mappe automatiquement les sources vers les commandes Twilio Studio :

### 🏘️ Affiches par Village

| Source Admin | Message Généré | Description |
|-------------|----------------|-------------|
| `GOMBE` | `START_AFF_GOMBE` | Affiche village Gombe |
| `MASINA` | `START_AFF_MASINA` | Affiche village Masina |
| `LEMBA` | `START_AFF_LEMBA` | Affiche village Lemba |
| `BANDA` | `START_AFF_BANDA` | Affiche village Banda |
| `NGALIEMA` | `START_AFF_NGALI` | Affiche village Ngaliema |

Tu peux aussi utiliser : `AFFICHE_GOMBE`, `AFFICHE_MASINA`, etc.

### 🏪 Points de Vente Partenaires

| Source Admin | Message Généré | Description |
|-------------|----------------|-------------|
| `BRACONGO` | `START_PDV_BRACONGO` | PDV Bracongo |
| `VODACOM` | `START_PDV_VODACOM` | PDV Vodacom |
| `ORANGE` | `START_PDV_ORANGE` | PDV Orange |
| `AIRTEL` | `START_PDV_AIRTEL` | PDV Airtel |

Tu peux aussi utiliser : `PDV_BRACONGO`, `PDV_VODACOM`, etc.

### 📱 Digital / Réseaux Sociaux

| Source Admin | Message Généré | Description |
|-------------|----------------|-------------|
| `FACEBOOK` ou `FB` | `START_FB` | Campagne Facebook |
| `INSTAGRAM` ou `IG` | `START_IG` | Campagne Instagram |
| `TIKTOK` | `START_TIKTOK` | Campagne TikTok |
| `WHATSAPP_STATUS` | `START_WA_STATUS` | Status WhatsApp |

### 📄 Flyers

| Source Admin | Message Généré | Description |
|-------------|----------------|-------------|
| `UNIVERSITE` | `START_FLYER_UNI` | Flyer université |
| `RUE` | `START_FLYER_RUE` | Flyer distribution rue |
| `EVENEMENT` | `START_FLYER_EVENT` | Flyer événement |

Tu peux aussi utiliser : `FLYER_UNIVERSITE`, `FLYER_RUE`, `FLYER_EVENEMENT`

---

## 🧪 Tester un QR Code

### Test 1 : Avec un vrai smartphone

1. **Imprime ou affiche le QR code** sur ton écran secondaire
2. **Scanne avec ton téléphone** (caméra ou app QR)
3. **Vérifie** :
   - ✅ WhatsApp s'ouvre automatiquement
   - ✅ Le chat avec +243841622222 est ouvert
   - ✅ Le message `START_AFF_GOMBE` est pré-rempli
   - ✅ Tu n'as qu'à appuyer sur "Envoyer"
4. **Envoie le message**
5. **Le bot doit répondre** avec le message d'accueil

### Test 2 : Avec un navigateur

1. **Récupère l'URL du QR code** :
   ```
   https://wabracongo.ywcdigital.com/qr/CODE_UNIQUE
   ```

2. **Ouvre l'URL dans ton navigateur mobile**

3. **Vérifie la redirection** :
   ```
   https://wa.me/243841622222?text=START_AFF_GOMBE
   ```

### Test 3 : Vérifier le compteur

1. **Scanne le QR code** plusieurs fois
2. **Va dans l'admin** : https://wabracongo.ywcdigital.com/admin/qrcodes
3. **Le compteur "Scans"** doit augmenter

---

## 🎨 Personnaliser les QR Codes

### Ajouter un Logo (Bracongo, Vodacom, etc.)

Tu peux modifier le QR code avec un outil comme :
- **Canva** : Ajouter le logo au centre
- **Photoshop** : Superposer le logo
- **Online QR Code Generator** avec logo

**Exemple de disposition :**
```
╔══════════════════════╗
║   [QR CODE IMAGE]    ║
║                      ║
║   🦁 CAN 2025 🦁    ║
║  Scanne pour gagner  ║
║                      ║
║  [LOGO BRACONGO]     ║
╚══════════════════════╝
```

---

## 📊 Suivre les Performances

### Dashboard QR Codes

1. **Nombre de scans par QR code**
   - Admin → QR Codes
   - Colonne "Scans"

2. **Taux de conversion**
   - Scans → Opt-ins → Inscriptions
   - Admin → Analytics

3. **Meilleure source**
   - Admin → Analytics
   - Section "Inscriptions par Source"

---

## 🔧 Configuration Avancée

### Changer le Numéro du Bot

Si ton numéro WhatsApp change, modifie dans :

**Fichier :** `app/Http/Controllers/Admin/QrCodeController.php`

**Ligne 150 :**
```php
$whatsappNumber = '243841622222'; // ← Remplace ici
```

### Ajouter une Nouvelle Source

**Fichier :** `app/Http/Controllers/Admin/QrCodeController.php`

**Lignes 169-207 :**

Ajoute une nouvelle ligne dans `$sourceMap` :

```php
'MA_NOUVELLE_SOURCE' => 'START_CUSTOM_MESSAGE',
```

---

## 🐛 Troubleshooting

### Problème 1 : QR code ne redirige pas

**Symptôme :** Le QR code ne fait rien ou erreur 404

**Solution :**
```bash
# Vérifier que la route existe
php artisan route:list | grep qr

# Résultat attendu :
GET /qr/{code} ... QrCodeController@scan
```

### Problème 2 : Message pas au bon format

**Symptôme :** Le message envoyé n'est pas reconnu par le bot

**Vérification :**
1. Scanne le QR code
2. Regarde le message pré-rempli dans WhatsApp
3. Il doit être exactement : `START_AFF_GOMBE` (ou autre commande valide)

**Si ce n'est pas le cas :**
- Vérifie la source du QR code dans l'admin
- Vérifie le mapping dans `generateStartMessage()` (ligne 163)

### Problème 3 : WhatsApp demande de choisir un contact

**Cause :** Format du lien incorrect

**Vérification :**
1. Ouvre l'URL du QR code dans un navigateur
2. Tu devrais être redirigé vers :
   ```
   https://wa.me/243841622222?text=START_AFF_GOMBE
   ```
3. Si ce n'est pas le cas, le numéro est mal formaté

**Solution :**
- Le numéro doit être sans le `+` : `243841622222`
- Pas d'espaces, pas de tirets

### Problème 4 : Le bot ne répond pas

**Cause :** Flow Twilio Studio pas configuré ou pas publié

**Solution :**
1. Va sur Twilio Console → Studio → Flows
2. Vérifie que le flow "CAN 2025 Kinshasa - Production" est publié
3. Vérifie que ton numéro WhatsApp est configuré pour utiliser ce flow
4. Teste avec un message direct : envoie `START_AFF_GOMBE` au bot

---

## 📈 Statistiques à Suivre

### Métriques Clés

1. **Nombre de QR codes créés** : Admin → QR Codes
2. **Total de scans** : Somme des compteurs
3. **Taux de scan → inscription** : Analytics
4. **Meilleure source** : Source avec le plus d'inscriptions
5. **QR codes inactifs** : À supprimer ou réactiver

### Objectifs

- ✅ Au moins **100 scans** par QR code
- ✅ Taux de conversion **scan → inscription > 50%**
- ✅ QR codes actifs dans **au moins 5 villages**
- ✅ QR codes chez **au moins 3 partenaires**

---

## 🎯 Bonnes Pratiques

### Placement des QR Codes

**✅ BON :**
- Hauteur des yeux (1,50m - 1,70m)
- Éclairage suffisant
- Surface plane et propre
- Taille minimum : 5cm x 5cm
- Instructions claires à côté

**❌ MAUVAIS :**
- Trop haut ou trop bas
- Derrière une vitre sale
- Trop petit (< 3cm)
- Sans instruction
- Mal imprimé (flou)

### Message d'accompagnement

**Exemple d'affiche :**
```
═══════════════════════════════
    🦁 CAN 2025 KINSHASA 🦁

     SCANNE ET GAGNE !

       [  QR CODE  ]

  📱 1. Scanne ce QR code
  💬 2. Envoie le message
  🎁 3. Gagne des cadeaux !

    Village : GOMBE

  Partenaire : BRACONGO
═══════════════════════════════
```

---

## ✅ Checklist de Déploiement

Avant d'imprimer et distribuer les QR codes :

- [ ] ✅ Code corrigé et déployé sur le serveur
- [ ] ✅ Flow Twilio Studio importé et publié
- [ ] ✅ Numéro WhatsApp (+243841622222) configuré
- [ ] ✅ Test réussi : scan → WhatsApp → message → bot répond
- [ ] ✅ Au moins 5 QR codes créés (un par village)
- [ ] ✅ QR codes téléchargés et sauvegardés
- [ ] ✅ Affiches design avec instructions claires
- [ ] ✅ Supports d'impression validés

---

**🎉 Les QR Codes sont maintenant parfaitement configurés !**

**Prochaine étape :** Imprime et distribue les QR codes dans les villages 🚀
