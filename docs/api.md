# Documentation API - ENI-Sortir

## 📋 Vue d'ensemble

Cette documentation présente l'API REST de l'application ENI-Sortir. L'API permet d'interagir avec toutes les fonctionnalités de l'application via des endpoints HTTP standardisés.

## 🔐 Authentification

### Méthodes d'authentification
- **Session-based** : Authentification par session pour l'interface web
- **Token-based** : Support des tokens pour les applications mobiles (à implémenter)

### Headers requis
```http
Content-Type: application/json
X-Requested-With: XMLHttpRequest
```

## 🏠 Endpoints principaux

### Accueil et navigation

#### GET /
**Description** : Page d'accueil avec liste des sorties
**Paramètres de requête** :
- `site` (int) : ID du site pour filtrer
- `etat` (string) : État des sorties à afficher
- `periode` (string) : Période (passées, futures, etc.)
- `recherche` (string) : Terme de recherche
- `organisateur` (bool) : Afficher seulement les sorties de l'utilisateur connecté
- `inscrit` (bool) : Afficher seulement les sorties où l'utilisateur est inscrit
- `non_inscrit` (bool) : Afficher seulement les sorties où l'utilisateur n'est pas inscrit
- `passees` (bool) : Inclure les sorties passées

**Réponse** : Page HTML avec liste des sorties filtrées

---

## 👥 Gestion des participants

### GET /profil
**Description** : Afficher le profil de l'utilisateur connecté
**Authentification** : Requise (ROLE_USER)
**Réponse** : Page HTML du profil

### POST /profil
**Description** : Modifier le profil de l'utilisateur
**Authentification** : Requise (ROLE_USER)
**Body** :
```json
{
  "nom": "string",
  "prenom": "string",
  "telephone": "string",
  "mail": "string",
  "site": "int"
}
```

### GET /profil/changer-mot-de-passe
**Description** : Formulaire de changement de mot de passe
**Authentification** : Requise (ROLE_USER)

### POST /profil/changer-mot-de-passe
**Description** : Changer le mot de passe
**Authentification** : Requise (ROLE_USER)
**Body** :
```json
{
  "currentPassword": "string",
  "newPassword": "string",
  "confirmPassword": "string"
}
```

---

## 🎯 Gestion des sorties

### GET /sorties/nouvelle
**Description** : Formulaire de création d'une nouvelle sortie
**Authentification** : Requise (ROLE_USER)
**Réponse** : Page HTML avec formulaire

### POST /sorties/nouvelle
**Description** : Créer une nouvelle sortie
**Authentification** : Requise (ROLE_USER)
**Body** :
```json
{
  "nom": "string",
  "dateHeureDebut": "datetime",
  "dateLimiteInscription": "datetime",
  "nbInscriptionsMax": "int",
  "duree": "int",
  "infosSortie": "string",
  "lieu": "int",
  "urlPhoto": "string"
}
```

### GET /sorties/{id}
**Description** : Afficher les détails d'une sortie
**Paramètres** :
- `id` (int) : ID de la sortie
**Réponse** : Page HTML avec détails de la sortie

### GET /sorties/{id}/editer
**Description** : Formulaire d'édition d'une sortie
**Authentification** : Requise (permission EDIT sur la sortie)
**Paramètres** :
- `id` (int) : ID de la sortie

### POST /sorties/{id}/editer
**Description** : Modifier une sortie existante
**Authentification** : Requise (permission EDIT sur la sortie)
**Body** : Même structure que la création

### POST /sorties/{id}/publier
**Description** : Publier une sortie (Créée → Ouverte)
**Authentification** : Requise (permission PUBLISH sur la sortie)
**Réponse** : Redirection vers la page de la sortie

### POST /sorties/{id}/annuler
**Description** : Annuler une sortie
**Authentification** : Requise (permission CANCEL sur la sortie)
**Réponse** : Redirection vers la page de la sortie

### POST /sorties/{id}/inscrire
**Description** : S'inscrire à une sortie
**Authentification** : Requise (ROLE_USER)
**Réponse** : Redirection vers la page de la sortie

### POST /sorties/{id}/desister
**Description** : Se désinscrire d'une sortie
**Authentification** : Requise (ROLE_USER)
**Réponse** : Redirection vers la page de la sortie

### POST /sorties/{id}/supprimer
**Description** : Supprimer une sortie
**Authentification** : Requise (permission DELETE sur la sortie)
**Réponse** : Redirection vers la page d'accueil

---

## 🗺️ Gestion des lieux

### GET /lieux
**Description** : Liste des lieux disponibles
**Réponse** : Page HTML avec liste des lieux

### GET /lieux/nouveau
**Description** : Formulaire de création d'un nouveau lieu
**Authentification** : Requise (ROLE_USER)

### POST /lieux/nouveau
**Description** : Créer un nouveau lieu
**Authentification** : Requise (ROLE_USER)
**Body** :
```json
{
  "nom": "string",
  "rue": "string",
  "latitude": "float",
  "longitude": "float",
  "ville": "int"
}
```

### GET /lieux/{id}
**Description** : Détails d'un lieu
**Paramètres** :
- `id` (int) : ID du lieu

### GET /lieux/{id}/editer
**Description** : Formulaire d'édition d'un lieu
**Authentification** : Requise (ROLE_USER)

### POST /lieux/{id}/editer
**Description** : Modifier un lieu
**Authentification** : Requise (ROLE_USER)

### POST /lieux/{id}/supprimer
**Description** : Supprimer un lieu
**Authentification** : Requise (ROLE_USER)

---

## 🔧 Administration

### GET /admin
**Description** : Tableau de bord administrateur
**Authentification** : Requise (ROLE_ADMIN)

### GET /admin/villes
**Description** : Gestion des villes
**Authentification** : Requise (ROLE_ADMIN)

### POST /admin/villes
**Description** : Créer une nouvelle ville
**Authentification** : Requise (ROLE_ADMIN)
**Body** :
```json
{
  "nom": "string",
  "codePostal": "string"
}
```

### GET /admin/villes/{id}/editer
**Description** : Éditer une ville
**Authentification** : Requise (ROLE_ADMIN)

### POST /admin/villes/{id}/editer
**Description** : Modifier une ville
**Authentification** : Requise (ROLE_ADMIN)

### POST /admin/villes/{id}/supprimer
**Description** : Supprimer une ville
**Authentification** : Requise (ROLE_ADMIN)

### GET /admin/sites
**Description** : Gestion des sites
**Authentification** : Requise (ROLE_ADMIN)

### POST /admin/sites
**Description** : Créer un nouveau site
**Authentification** : Requise (ROLE_ADMIN)
**Body** :
```json
{
  "nom": "string"
}
```

### GET /admin/participants
**Description** : Liste des participants
**Authentification** : Requise (ROLE_ADMIN)

### POST /admin/participants/{id}/activer
**Description** : Activer un participant
**Authentification** : Requise (ROLE_ADMIN)

### POST /admin/participants/{id}/desactiver
**Description** : Désactiver un participant
**Authentification** : Requise (ROLE_ADMIN)

### POST /admin/participants/{id}/reinitialiser-mdp
**Description** : Réinitialiser le mot de passe d'un participant
**Authentification** : Requise (ROLE_ADMIN)

### GET /admin/sorties
**Description** : Gestion des sorties
**Authentification** : Requise (ROLE_ADMIN)

### POST /admin/sorties/{id}/annuler
**Description** : Annuler une sortie (action admin)
**Authentification** : Requise (ROLE_ADMIN)

---

## 🔄 Tâches automatiques

### GET /cron
**Description** : Interface de surveillance des tâches automatiques
**Authentification** : Requise (ROLE_ADMIN)

### GET /cron/simulate
**Description** : Simuler les transitions automatiques
**Authentification** : Requise (ROLE_ADMIN)

### POST /cron/execute
**Description** : Exécuter les transitions automatiques
**Authentification** : Requise (ROLE_ADMIN)

---

## 🗺️ API Cartographique

### GET /map
**Description** : Interface de sélection sur carte
**Authentification** : Requise (ROLE_USER)

### GET /events-map
**Description** : Carte des événements
**Réponse** : Page HTML avec carte interactive

---

## 📊 Codes de réponse HTTP

### Succès
- `200 OK` : Requête réussie
- `201 Created` : Ressource créée avec succès
- `204 No Content` : Action réussie sans contenu de retour

### Redirection
- `302 Found` : Redirection temporaire (après POST)
- `301 Moved Permanently` : Redirection permanente

### Erreurs client
- `400 Bad Request` : Requête malformée
- `401 Unauthorized` : Non authentifié
- `403 Forbidden` : Non autorisé
- `404 Not Found` : Ressource non trouvée
- `422 Unprocessable Entity` : Erreur de validation

### Erreurs serveur
- `500 Internal Server Error` : Erreur interne du serveur

---

## 🔍 Filtres et recherche

### Filtres de sorties
Les endpoints de liste des sorties supportent les paramètres suivants :

```http
GET /?site=1&etat=Ouverte&periode=futures&recherche=château&organisateur=1
```

**Paramètres disponibles** :
- `site` : Filtrer par site ENI
- `etat` : Filtrer par état (Créée, Ouverte, Clôturée, etc.)
- `periode` : Période (futures, passees, aujourdhui)
- `recherche` : Recherche textuelle dans le nom et description
- `organisateur` : Sorties organisées par l'utilisateur connecté
- `inscrit` : Sorties où l'utilisateur est inscrit
- `non_inscrit` : Sorties où l'utilisateur n'est pas inscrit
- `passees` : Inclure les sorties passées

---

## 📝 Validation des données

### Contraintes de validation

#### Sortie
- `nom` : Obligatoire, max 30 caractères
- `dateHeureDebut` : Obligatoire, doit être dans le futur
- `dateLimiteInscription` : Obligatoire, doit être antérieure à la date de début
- `nbInscriptionsMax` : Obligatoire, minimum 1, maximum 100
- `duree` : Optionnel, maximum 1440 minutes (24h)
- `infosSortie` : Optionnel, max 500 caractères
- `lieu` : Obligatoire, doit exister
- `urlPhoto` : Optionnel, doit être une URL valide

#### Participant
- `pseudo` : Obligatoire, unique, max 30 caractères
- `nom` : Obligatoire, max 30 caractères
- `prenom` : Obligatoire, max 30 caractères
- `mail` : Obligatoire, unique, format email valide
- `telephone` : Optionnel, max 15 caractères
- `motDePasse` : Obligatoire, minimum 6 caractères

#### Lieu
- `nom` : Obligatoire, max 30 caractères
- `rue` : Optionnel, max 30 caractères
- `latitude` : Optionnel, format décimal
- `longitude` : Optionnel, format décimal
- `ville` : Obligatoire, doit exister

---

## 🔒 Permissions et autorisations

### Rôles
- `ROLE_USER` : Utilisateur standard
- `ROLE_ADMIN` : Administrateur

### Permissions sur les sorties
- `EDIT` : Modifier une sortie (organisateur ou admin)
- `PUBLISH` : Publier une sortie (organisateur ou admin)
- `CANCEL` : Annuler une sortie (organisateur ou admin)
- `DELETE` : Supprimer une sortie (organisateur ou admin)

### Guards de workflow
Les transitions de workflow sont protégées par des guards :
- `publier` : Organisateur ou admin
- `annuler` : Organisateur ou admin
- `clore_auto` : Automatique
- `lancer` : Automatique
- `terminer` : Automatique
- `archiver` : Automatique

---

## 📱 Support mobile

### Responsive design
L'interface est entièrement responsive et s'adapte aux écrans mobiles.

### Endpoints mobiles
- `/mobile/` : Interface mobile optimisée
- Support des gestes tactiles
- Interface adaptée aux petits écrans

---

## 🧪 Tests API

### Endpoints de test
- `/test-business-rules` : Tests des règles métier
- `/test-map` : Tests de l'interface cartographique

### Données de test
Les fixtures incluent des données de test complètes pour tous les scénarios.

---

## 📈 Monitoring et logs

### Logs d'application
- Logs des transitions de workflow
- Logs des inscriptions/désinscriptions
- Logs des actions administratives

### Métriques
- Nombre de sorties par état
- Statistiques d'inscriptions
- Performance des requêtes

---

## 🔄 Webhooks (à implémenter)

### Événements supportés
- `sortie.created` : Sortie créée
- `sortie.published` : Sortie publiée
- `sortie.cancelled` : Sortie annulée
- `inscription.created` : Inscription créée
- `inscription.cancelled` : Inscription annulée

---

*Cette documentation est mise à jour régulièrement. Pour toute question ou suggestion d'amélioration, contactez l'équipe de développement.*

