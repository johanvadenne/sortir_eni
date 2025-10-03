# Configuration CRON pour les transitions automatiques

## Vue d'ensemble

La commande `app:sortie:tick` doit être exécutée toutes les 5 minutes pour traiter les transitions automatiques des sorties.

## Transitions automatiques

1. **Ouverte → Clôturée** :
   - Nombre max d'inscriptions atteint
   - Date limite d'inscription dépassée

2. **Clôturée → Activité en cours** :
   - Date de début de la sortie atteinte

3. **Activité en cours → Activité terminée** :
   - Durée de la sortie écoulée (si définie)

4. **Activité terminée → Activité historisée** :
   - 1 mois après la date de début de la sortie

## Configuration CRON

### 1. Éditer le crontab

```bash
crontab -e
```

### 2. Ajouter la ligne CRON

```bash
# Exécuter toutes les 5 minutes
*/5 * * * * cd /chemin/vers/votre/projet && php bin/console app:sortie:tick >> /var/log/sortie-tick.log 2>&1
```

### 3. Exemple avec chemin complet

```bash
# Exemple avec chemin absolu
*/5 * * * * cd /home/user/sortir && php bin/console app:sortie:tick >> /var/log/sortie-tick.log 2>&1
```

### 4. Avec gestion des erreurs

```bash
# Version avec gestion des erreurs et notifications
*/5 * * * * cd /home/user/sortir && php bin/console app:sortie:tick >> /var/log/sortie-tick.log 2>&1 || echo "Erreur dans app:sortie:tick" | mail -s "Erreur CRON Sortie" admin@example.com
```

## Options de la commande

### Mode dry-run (test)

```bash
php bin/console app:sortie:tick --dry-run
```

Affiche les actions qui seraient effectuées sans les exécuter.

### Mode verbose

```bash
php bin/console app:sortie:tick --verbose
```

Affiche les détails de chaque transition.

### Combinaison des options

```bash
php bin/console app:sortie:tick --dry-run --verbose
```

## Surveillance et logs

### 1. Vérifier les logs

```bash
tail -f /var/log/sortie-tick.log
```

### 2. Vérifier le statut CRON

```bash
# Vérifier si le service CRON est actif
systemctl status cron

# Voir les tâches CRON planifiées
crontab -l
```

### 3. Test manuel

```bash
# Tester la commande manuellement
php bin/console app:sortie:tick --dry-run --verbose
```

## Configuration avancée

### 1. Avec variables d'environnement

```bash
*/5 * * * * cd /home/user/sortir && APP_ENV=prod php bin/console app:sortie:tick >> /var/log/sortie-tick.log 2>&1
```

### 2. Avec rotation des logs

```bash
# Utiliser logrotate pour gérer les logs
*/5 * * * * cd /home/user/sortir && php bin/console app:sortie:tick >> /var/log/sortie-tick.log 2>&1
```

### 3. Avec monitoring

```bash
# Version avec monitoring et alertes
*/5 * * * * cd /home/user/sortir && php bin/console app:sortie:tick >> /var/log/sortie-tick.log 2>&1 || curl -X POST "https://hooks.slack.com/your-webhook" -d '{"text":"Erreur dans app:sortie:tick"}'
```

## Dépannage

### 1. Problèmes de permissions

```bash
# Vérifier les permissions
ls -la bin/console
chmod +x bin/console
```

### 2. Problèmes de chemin

```bash
# Utiliser le chemin absolu
which php
/usr/bin/php /chemin/absolu/vers/projet/bin/console app:sortie:tick
```

### 3. Problèmes d'environnement

```bash
# Spécifier l'environnement
APP_ENV=prod php bin/console app:sortie:tick
```

## Exemple de configuration complète

```bash
# Configuration CRON complète
*/5 * * * * cd /home/user/sortir && APP_ENV=prod php bin/console app:sortie:tick >> /var/log/sortie-tick.log 2>&1

# Nettoyage des logs (quotidien à 2h du matin)
0 2 * * * find /var/log -name "sortie-tick.log*" -mtime +30 -delete
```

## Monitoring

### 1. Vérifier l'exécution

```bash
# Vérifier les dernières exécutions
grep "$(date +%Y-%m-%d)" /var/log/sortie-tick.log
```

### 2. Statistiques

```bash
# Compter les transitions par jour
grep "transitions automatiques traitées" /var/log/sortie-tick.log | tail -10
```

### 3. Alertes

```bash
# Script de monitoring
#!/bin/bash
if ! pgrep -f "app:sortie:tick" > /dev/null; then
    echo "CRON app:sortie:tick ne semble pas s'exécuter" | mail -s "Alerte CRON" admin@example.com
fi
```
