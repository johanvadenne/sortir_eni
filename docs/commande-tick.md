# Commande app:sortie:tick

## Vue d'ensemble

La commande `app:sortie:tick` traite automatiquement les transitions d'état des sorties selon les règles métier définies.

## Utilisation

### Commande de base

```bash
php bin/console app:sortie:tick
```

### Options disponibles

- `--dry-run` : Affiche les actions sans les exécuter (mode test)
- `--details` ou `-d` : Affiche les détails des opérations
- `--help` ou `-h` : Affiche l'aide de la commande

### Exemples d'utilisation

#### Test sans exécution

```bash
php bin/console app:sortie:tick --dry-run
```

#### Test avec détails

```bash
php bin/console app:sortie:tick --dry-run --details
```

#### Exécution normale

```bash
php bin/console app:sortie:tick
```

#### Exécution avec détails

```bash
php bin/console app:sortie:tick --details
```

## Transitions automatiques

### 1. Ouverte → Clôturée

**Conditions :**
- Nombre maximum d'inscriptions atteint
- Date limite d'inscription dépassée

**Logique :**
```php
if ($sortie->isComplete() || $sortie->getDateLimiteInscription() < new \DateTime()) {
    // Clôturer la sortie
}
```

### 2. Clôturée → Activité en cours

**Conditions :**
- Date de début de la sortie atteinte

**Logique :**
```php
if ($sortie->getDateHeureDebut() <= new \DateTime()) {
    // Démarrer la sortie
}
```

### 3. Activité en cours → Activité terminée

**Conditions :**
- Durée de la sortie écoulée (si définie)

**Logique :**
```php
if ($sortie->getDuree() !== null) {
    $finSortie = clone $sortie->getDateHeureDebut();
    $finSortie->modify("+{$sortie->getDuree()} minutes");

    if ($now >= $finSortie) {
        // Terminer la sortie
    }
}
```

### 4. Activité terminée → Activité historisée

**Conditions :**
- 1 mois après la date de début de la sortie

**Logique :**
```php
$dateArchivage = clone $sortie->getDateHeureDebut();
$dateArchivage->modify('+1 month');

if ($now >= $dateArchivage) {
    // Historiser la sortie
}
```

## Sortie de la commande

### Format standard

```
Traitement des transitions automatiques des sorties
===================================================

 Heure actuelle : 22/09/2025 10:31:00

1. Traitement : Ouverte → Clôturée
----------------------------------

 Sorties à clôturer : 2

2. Traitement : Clôturée → Activité en cours
--------------------------------------------

 Sorties à démarrer : 1

3. Traitement : Activité en cours → Activité terminée
-----------------------------------------------------

 Sorties à terminer : 0

4. Traitement : Activité terminée → Activité historisée
-------------------------------------------------------

 Sorties à historiser : 3

Résumé des transitions
----------------------

 ----------------------------------------- --------
  Transition                                Nombre
 ----------------------------------------- --------
  Ouverte → Clôturée                        2
  Clôturée → Activité en cours              1
  Activité en cours → Activité terminée     0
  Activité terminée → Activité historisée   3
 ----------------------------------------- --------


 [OK] 6 transitions automatiques traitées
```

### Format avec détails

```
Traitement des transitions automatiques des sorties
===================================================

 Heure actuelle : 22/09/2025 10:31:00

1. Traitement : Ouverte → Clôturée
----------------------------------

• Sortie de test 1 : Nombre max atteint
• Sortie de test 2 : Date limite dépassée

 Sorties à clôturer : 2

2. Traitement : Clôturée → Activité en cours
--------------------------------------------

• Sortie de test 3 : Date de début atteinte

 Sorties à démarrer : 1

3. Traitement : Activité en cours → Activité terminée
-----------------------------------------------------

 Sorties à terminer : 0

4. Traitement : Activité terminée → Activité historisée
-------------------------------------------------------

• Sortie de test 4 : 1 mois écoulé depuis la date de début
• Sortie de test 5 : 1 mois écoulé depuis la date de début
• Sortie de test 6 : 1 mois écoulé depuis la date de début

 Sorties à historiser : 3

Résumé des transitions
----------------------

 ----------------------------------------- --------
  Transition                                Nombre
 ----------------------------------------- --------
  Ouverte → Clôturée                        2
  Clôturée → Activité en cours              1
  Activité en cours → Activité terminée     0
  Activité terminée → Activité historisée   3
 ----------------------------------------- --------


 [OK] 6 transitions automatiques traitées
```

## Gestion des erreurs

### Erreurs de transition

Si une transition échoue, l'erreur est capturée et affichée :

```
Erreurs rencontrées :
• Impossible de clôturer la sortie : Sortie de test
• Erreur lors du démarrage de Sortie de test 2 : Transition non autorisée
```

### Codes de retour

- `0` : Succès
- `1` : Erreur

## Intégration avec CRON

### Configuration CRON

```bash
# Exécution toutes les 5 minutes
*/5 * * * * cd /chemin/vers/projet && php bin/console app:sortie:tick >> /var/log/sortie-tick.log 2>&1
```

### Surveillance

```bash
# Vérifier les logs
tail -f /var/log/sortie-tick.log

# Vérifier les dernières exécutions
grep "$(date +%Y-%m-%d)" /var/log/sortie-tick.log
```

## Tests et développement

### Test en mode dry-run

```bash
# Tester sans modifier les données
php bin/console app:sortie:tick --dry-run --details
```

### Test avec des données spécifiques

```bash
# Tester avec un environnement spécifique
APP_ENV=test php bin/console app:sortie:tick --dry-run
```

### Debug

```bash
# Mode debug avec plus de détails
php bin/console app:sortie:tick --details -vvv
```

## Performance

### Optimisations

- Utilisation de requêtes DQL optimisées
- Traitement par lot des transitions
- Gestion des erreurs sans interruption

### Monitoring

```bash
# Mesurer le temps d'exécution
time php bin/console app:sortie:tick
```

## Sécurité

### Permissions

- La commande doit être exécutée avec les permissions appropriées
- Accès en lecture/écriture à la base de données
- Accès aux logs

### Validation

- Vérification des pré-conditions avant chaque transition
- Gestion des exceptions
- Logging des erreurs

## Maintenance

### Nettoyage des logs

```bash
# Nettoyer les logs anciens
find /var/log -name "sortie-tick.log*" -mtime +30 -delete
```

### Rotation des logs

```bash
# Configuration logrotate
/var/log/sortie-tick.log {
    daily
    rotate 30
    compress
    delaycompress
    missingok
    notifempty
}
```
