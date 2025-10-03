# Fixtures - Jeu de données de test

Ce document décrit les fixtures (données de test) créées pour l'application Sortir.

## Chargement des fixtures

Pour charger les fixtures, utilisez la commande suivante :

```bash
php bin/console doctrine:fixtures:load --no-interaction
```

⚠️ **Attention** : Cette commande supprime toutes les données existantes et les remplace par les données de test.

## Données créées

### 1. États des sorties (EtatFixtures)

7 états sont créés selon le workflow défini :

- **Créée** : Sortie créée mais pas encore publiée
- **Ouverte** : Inscriptions ouvertes
- **Clôturée** : Inscriptions fermées
- **En cours** : Activité en cours
- **Terminée** : Activité terminée
- **Annulée** : Sortie annulée
- **Historisée** : Sortie archivée

### 2. Sites ENI (SiteFixtures)

6 sites ENI sont créés :

- ENI Nantes
- ENI Rennes
- ENI Angers
- ENI Le Mans
- ENI Tours
- ENI Orléans

### 3. Villes (VilleFixtures)

9 villes françaises avec leurs codes postaux :

- Nantes (44000)
- Rennes (35000)
- Angers (49000)
- Le Mans (72000)
- Tours (37000)
- Orléans (45000)
- Paris (75001)
- Lyon (69001)
- Marseille (13001)

### 4. Lieux (LieuFixtures)

14 lieux touristiques répartis dans les différentes villes :

#### Nantes
- Château des Ducs de Bretagne
- Jardin des Plantes
- Île de Nantes

#### Rennes
- Parc du Thabor
- Place de la Mairie

#### Angers
- Château d'Angers
- Jardin des Plantes d'Angers

#### Le Mans
- Circuit de la Sarthe

#### Tours
- Place Plumereau

#### Orléans
- Place du Martroi

#### Paris
- Tour Eiffel
- Louvre

#### Lyon
- Place Bellecour

#### Marseille
- Vieux Port

### 5. Participants (ParticipantFixtures)

4 participants sont créés :

#### Administrateur
- **Pseudo** : admin
- **Email** : admin@eni.fr
- **Mot de passe** : admin123
- **Rôle** : Administrateur
- **Site** : ENI Nantes
- **Statut** : Actif

#### Utilisateurs normaux
1. **jean.dupont**
   - Email : jean.dupont@eni.fr
   - Mot de passe : password123
   - Site : ENI Nantes

2. **marie.martin**
   - Email : marie.martin@eni.fr
   - Mot de passe : password123
   - Site : ENI Nantes

3. **pierre.bernard**
   - Email : pierre.bernard@eni.fr
   - Mot de passe : password123
   - Site : ENI Rennes

### 6. Sorties (SortieFixtures)

3 sorties avec différents états :

#### 1. Visite du Château de Nantes
- **État** : Ouverte
- **Organisateur** : jean.dupont
- **Date** : Dans 1 semaine
- **Durée** : 120 minutes
- **Places** : 15
- **Lieu** : Château des Ducs de Bretagne

#### 2. Randonnée au Parc du Thabor
- **État** : Ouverte
- **Organisateur** : marie.martin
- **Date** : Dans 2 semaines
- **Durée** : 180 minutes
- **Places** : 20
- **Lieu** : Parc du Thabor

#### 3. Visite d'Angers
- **État** : Clôturée
- **Organisateur** : pierre.bernard
- **Date** : Dans 3 jours
- **Durée** : 90 minutes
- **Places** : 12
- **Lieu** : Château d'Angers

### 7. Inscriptions (InscriptionFixtures)

7 inscriptions réparties sur les différentes sorties :

- **Visite du Château de Nantes** : marie.martin, pierre.bernard
- **Randonnée au Parc du Thabor** : jean.dupont, admin
- **Visite d'Angers** : jean.dupont, marie.martin, admin

## Structure des fixtures

### MainFixtures.php

La classe `MainFixtures` centralise le chargement de toutes les données dans l'ordre correct :

1. États
2. Sites
3. Villes
4. Lieux (dépendent des villes)
5. Participants (dépendent des sites)
6. Sorties (dépendent des états, lieux et participants)
7. Inscriptions (dépendent des sorties et participants)

### Gestion des références

Les fixtures utilisent le système de références de DoctrineFixturesBundle pour créer des liens entre les entités :

```php
// Création d'une référence
$this->addReference('participant_admin', $participant);

// Utilisation d'une référence
$participant = $this->getReference('participant_admin', Participant::class);
```

### Mots de passe

Tous les mots de passe sont hashés avec le service `UserPasswordHasherInterface` :

- **Administrateur** : admin123
- **Utilisateurs** : password123

## Utilisation pour les tests

Ces fixtures fournissent un jeu de données complet pour :

- Tester l'authentification (admin/utilisateur)
- Tester les différents états des sorties
- Tester les inscriptions/désinscriptions
- Tester les permissions et autorisations
- Tester le workflow des sorties
- Valider l'interface utilisateur

## Maintenance

Pour ajouter de nouvelles données de test :

1. Modifier la classe `MainFixtures`
2. Respecter l'ordre des dépendances
3. Utiliser le système de références pour les relations
4. Recharger les fixtures avec `doctrine:fixtures:load`
