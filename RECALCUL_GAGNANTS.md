# Recalcul des Gagnants et Points

## 🎯 Objectif

Cette commande permet de recalculer tous les gagnants et les points des matchs terminés. Utilisez-la après une mise à jour du système de calcul des points ou pour corriger des erreurs.

## 📋 Deux Commandes Disponibles

### 1. Calcul Normal (Nouveaux Matchs)

```bash
php artisan pronostic:calculate-winners
```

**Utilisation :**
- Calculer automatiquement les gagnants des matchs qui viennent de se terminer
- Exécutée toutes les 5 minutes par le scheduler Laravel
- Ne recalcule PAS les matchs déjà traités

**Quand l'utiliser :**
- Opération automatique (via scheduler)
- Traitement quotidien normal

### 2. Recalcul Total (Tous les Matchs)

```bash
php artisan pronostic:recalculate-all --force
```

**Utilisation :**
- Recalcule TOUS les matchs terminés, même déjà calculés
- Met à jour les points avec la nouvelle logique
- Utile après une mise à jour du système

**Quand l'utiliser :**
- ✅ Après une mise à jour du code de calcul des points
- ✅ Pour corriger des erreurs dans les calculs précédents
- ✅ Après avoir ajouté le système de points (comme maintenant)
- ⚠️ À utiliser avec précaution en production

## 🔄 Résultats du Recalcul Récent

```
🔄 Recalcul de tous les gagnants...
📊 3 match(s) à recalculer

⚽ Cote d'ivoire vs Mali (2-3)
   📈 3 participant(s)
   🎯 0 score(s) exact(s) (10 pts)
   ✅ 0 bon(s) résultat(s) (5 pts)
   💰 0 points distribués

⚽ Algérie vs Tunisie (1-0)
   📈 1 participant(s)
   🎯 0 score(s) exact(s) (10 pts)
   ✅ 1 bon(s) résultat(s) (5 pts)  ← Josias Test a gagné !
   💰 5 points distribués

⚽ Burkina vs Ghana (0-1)
   📈 1 participant(s)
   🎯 0 score(s) exact(s) (10 pts)
   ✅ 0 bon(s) résultat(s) (5 pts)
   💰 0 points distribués

✅ Recalcul terminé !
🏆 Total gagnants: 1
💰 Total points distribués: 5 pts
```

## 📊 Statistiques Après Recalcul

### Dashboard Principal
- Total pronostics : **8**
- Total gagnants : **1**
- Total points distribués : **5 pts**

### Top Joueurs
1. 🥇 **Josias Test** - 5 pts (1 victoire / 4 pronostics)

### Par Match
- **Algérie vs Tunisie** (1-0) : 1 pronostic, **1 gagnant**, 5 points
- **Cote d'ivoire vs Mali** (2-3) : 3 pronostics, 0 gagnant, 0 points
- **Burkina vs Ghana** (0-1) : 1 pronostic, 0 gagnant, 0 points

## 🎮 Système de Points (Rappel)

| Type de Pronostic | Points Attribués |
|-------------------|------------------|
| Score exact (ex: 2-1 vs 2-1) | **10 points** |
| Bon résultat avec score (ex: 2-1 vs 3-0 = les deux victoire A) | **5 points** |
| Bon résultat simple (ex: team_a_win = victoire A) | **5 points** |
| Mauvais pronostic | **0 points** |

## 🛠️ Maintenance

### Vérifier les Matchs Non Calculés

```bash
php -r "
require 'vendor/autoload.php';
\$app = require 'bootstrap/app.php';
\$app->make('Illuminate\\Contracts\\Console\\Kernel')->bootstrap();

\$matches = App\\Models\\FootballMatch::where('status', 'finished')
    ->whereNotNull('score_a')
    ->whereNotNull('score_b')
    ->where('winners_calculated', false)
    ->get();

echo \"📊 Matchs terminés non calculés : \" . \$matches->count() . \"\\n\";
foreach (\$matches as \$m) {
    echo \"⚽ #{\$m->id}: {\$m->team_a} vs {\$m->team_b} ({\$m->score_a}-{\$m->score_b})\\n\";
}
"
```

### Recalculer un Match Spécifique

```bash
php artisan pronostic:calculate-winners --match=2
```

### Statistiques Rapides

```bash
php test_dashboard_stats.php
```

## ⚠️ Points d'Attention

### Avant le Recalcul

1. ✅ Vérifiez que tous les matchs ont des scores finaux corrects
2. ✅ Assurez-vous que la logique de calcul est correcte
3. ✅ Testez d'abord sur un match spécifique si possible

### Après le Recalcul

1. ✅ Vérifiez le dashboard : `/admin/dashboard`
2. ✅ Vérifiez les stats : `/admin/pronostics/stats`
3. ✅ Vérifiez le leaderboard : `/admin/leaderboard`
4. ✅ Vérifiez les logs : `tail -f storage/logs/laravel.log`

### En Production

⚠️ **Important :** Si vous recalculez en production avec `--force`, les utilisateurs qui ont déjà reçu des notifications pourraient recevoir de nouvelles notifications. Pour éviter cela, commentez temporairement la partie notification dans `CalculatePronosticWinners.php`.

## 📝 Logs

Tous les calculs sont loggés dans `storage/logs/laravel.log` :

```
[2025-12-16 00:00:00] local.INFO: Winners recalculated {"match_id":3,"participants":1,"winners":1,"points":5}
```

## 🚀 Scheduler Automatique

Le calcul automatique s'exécute toutes les 5 minutes via le scheduler Laravel :

```php
// bootstrap/app.php
$schedule->command('pronostic:calculate-winners')
    ->everyFiveMinutes()
    ->withoutOverlapping()
    ->runInBackground();
```

Pour vérifier que le scheduler fonctionne :

```bash
php artisan schedule:list
```

## 📞 Notifications WhatsApp

Lors du calcul normal (pas le recalcul), les gagnants reçoivent une notification :

**Score Exact (10 pts) :**
```
🎉 FÉLICITATIONS ! 🎉

Tu as GAGNÉ ton pronostic !

⚽ Match: RDC vs Maroc
📊 Score final: 2 - 1
✨ Points gagnés: 10 pts

🎯 SCORE EXACT ! Tu es un champion !

🎁 Tu as gagné: [Nom du prix] !
💰 Valeur: [Valeur] CDF
```

**Bon Résultat (5 pts) :**
```
🎉 FÉLICITATIONS ! 🎉

Tu as GAGNÉ ton pronostic !

⚽ Match: RDC vs Maroc
📊 Score final: 2 - 1
✨ Points gagnés: 5 pts

🏆 Continue comme ça pour gagner encore plus de prix !
```

## 🔧 Commandes Utiles

```bash
# Calculer uniquement les nouveaux matchs
php artisan pronostic:calculate-winners

# Recalculer tous les matchs (FORCE)
php artisan pronostic:recalculate-all --force

# Calculer un match spécifique
php artisan pronostic:calculate-winners --match=1

# Tester les statistiques
php test_dashboard_stats.php

# Vérifier la structure de la BD
php artisan db:table pronostics

# Voir les logs en temps réel
tail -f storage/logs/laravel.log
```

---

✅ **Le système de calcul des gagnants est maintenant opérationnel et à jour !**
