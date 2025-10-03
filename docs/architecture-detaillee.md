# 📁 Architecture détaillée - ENI-Sortir

## 📋 Vue d'ensemble

Ce document présente une analyse complète de l'architecture du projet ENI-Sortir, dossier par dossier et fichier par fichier. Chaque section explique le rôle, les fonctionnalités et l'importance des composants.

---

## 🗂️ Structure générale du projet

```
sortir/
├── src/                    # Code source principal
├── templates/              # Templates Twig
├── public/                 # Fichiers publics
├── config/                 # Configuration Symfony
├── migrations/             # Migrations de base de données
├── tests/                  # Tests (à développer)
├── docs/                   # Documentation
├── var/                    # Cache et logs
└── vendor/                 # Dépendances Composer
```

---

## 📁 **src/ - Code source principal**

### 🎯 **Rôle**
Le dossier `src/` contient tout le code source de l'application Symfony, organisé selon les conventions MVC et les bonnes pratiques Symfony.

### 📂 **Structure détaillée**

#### **src/Command/ - Commandes console**

**Rôle :** Automatisation et administration via ligne de commande

**Fichiers :**

##### `CreateAdminCommand.php`
- **Fonction :** Création d'utilisateurs administrateurs
- **Commande :** `php bin/console user:create-admin`
- **Fonctionnalités :**
  - ✅ Création interactive ou par paramètres
  - ✅ Validation des données (unicité pseudo/email)
  - ✅ Hachage sécurisé des mots de passe
  - ✅ Association automatique à un site ENI
  - ✅ Interface utilisateur élégante avec SymfonyStyle

##### `SortieTickCommand.php`
- **Fonction :** Automatisation du workflow des sorties
- **Commande :** `php bin/console app:sortie:tick`
- **Fonctionnalités :**
  - ✅ Transitions automatiques des états de sorties
  - ✅ Mode dry-run pour simulation
  - ✅ Rapports détaillés des opérations
  - ✅ Gestion d'erreurs robuste
  - ✅ Optimisé pour l'exécution cron

**Transitions gérées :**
1. **Ouverte → Clôturée** : Date limite d'inscription dépassée
2. **Clôturée → En cours** : Date de début atteinte
3. **En cours → Terminée** : Durée écoulée
4. **Terminée → Historisée** : Après 1 mois

---

#### **src/Controller/ - Contrôleurs MVC**

**Rôle :** Gestion des requêtes HTTP et logique de présentation

**Fichiers :**

##### `AdminController.php`
- **Fonction :** Interface d'administration
- **Routes :** `/admin/*`
- **Fonctionnalités :**
  - ✅ Tableau de bord administrateur
  - ✅ Gestion des participants (activation/désactivation)
  - ✅ Gestion des données de référence (villes, sites, lieux)
  - ✅ Monitoring des sorties
  - ✅ Interface de surveillance des transitions

##### `HomeController.php`
- **Fonction :** Page d'accueil et liste des sorties
- **Routes :** `/`, `/home`
- **Fonctionnalités :**
  - ✅ Affichage des sorties avec filtres
  - ✅ Recherche par nom, site, organisateur
  - ✅ Filtrage par état et dates
  - ✅ Pagination des résultats
  - ✅ Interface responsive

##### `SortieController.php`
- **Fonction :** Gestion complète des sorties
- **Routes :** `/sorties/*`
- **Fonctionnalités :**
  - ✅ CRUD complet (Créer, Lire, Modifier, Supprimer)
  - ✅ Workflow des sorties (publier, annuler)
  - ✅ Gestion des inscriptions/désinscriptions
  - ✅ Sécurité avec Voters personnalisés
  - ✅ Validation des permissions

##### `InscriptionController.php`
- **Fonction :** Gestion des inscriptions aux sorties
- **Routes :** `/inscriptions/*`
- **Fonctionnalités :**
  - ✅ Inscription/désinscription aux sorties
  - ✅ Validation des contraintes métier
  - ✅ Gestion des places disponibles
  - ✅ Notifications aux participants

##### `LieuController.php`
- **Fonction :** Gestion des lieux et géolocalisation
- **Routes :** `/lieux/*`
- **Fonctionnalités :**
  - ✅ CRUD des lieux
  - ✅ Intégration cartographique (Leaflet)
  - ✅ Sélection visuelle sur carte
  - ✅ Validation des coordonnées GPS

##### `ProfilController.php`
- **Fonction :** Gestion des profils utilisateurs
- **Routes :** `/profil/*`
- **Fonctionnalités :**
  - ✅ Modification du profil personnel
  - ✅ Upload de photos de profil
  - ✅ Changement de mot de passe
  - ✅ Gestion des préférences

##### `SecurityController.php`
- **Fonction :** Authentification et sécurité
- **Routes :** `/login`, `/logout`
- **Fonctionnalités :**
  - ✅ Connexion utilisateur
  - ✅ Déconnexion sécurisée
  - ✅ Gestion des erreurs d'authentification
  - ✅ Redirection post-connexion

##### `CronController.php`
- **Fonction :** Interface web pour les tâches automatiques
- **Routes :** `/cron/*`
- **Fonctionnalités :**
  - ✅ Simulation des transitions de workflow
  - ✅ Monitoring des tâches automatiques
  - ✅ Interface de test pour les développeurs
  - ✅ Rapports d'exécution

##### `EventMapController.php`
- **Fonction :** Carte interactive des événements
- **Routes :** `/events-map`
- **Fonctionnalités :**
  - ✅ Affichage des sorties sur carte
  - ✅ Filtrage géographique
  - ✅ Informations détaillées des sorties
  - ✅ Interface cartographique interactive

##### `MapController.php`
- **Fonction :** Gestion des cartes et géolocalisation
- **Routes :** `/map/*`
- **Fonctionnalités :**
  - ✅ Sélection de lieux sur carte
  - ✅ Géocodage d'adresses
  - ✅ Validation des coordonnées
  - ✅ Interface cartographique

##### `ServicesController.php`
- **Fonction :** Services utilitaires et API
- **Routes :** `/services/*`
- **Fonctionnalités :**
  - ✅ Endpoints API pour les services
  - ✅ Services métier exposés
  - ✅ Intégration avec applications externes

##### `TestBusinessRulesController.php`
- **Fonction :** Tests des règles métier
- **Routes :** `/test-business-rules`
- **Fonctionnalités :**
  - ✅ Interface de test des règles métier
  - ✅ Validation des contraintes
  - ✅ Tests des workflows
  - ✅ Outil de développement

##### `TestMapController.php`
- **Fonction :** Tests de l'intégration cartographique
- **Routes :** `/test-map`
- **Fonctionnalités :**
  - ✅ Tests des fonctionnalités de carte
  - ✅ Validation de la géolocalisation
  - ✅ Interface de test pour les développeurs

##### `WorkflowController.php`
- **Fonction :** Gestion du workflow des sorties
- **Routes :** `/workflow/*`
- **Fonctionnalités :**
  - ✅ Interface de gestion des transitions
  - ✅ Simulation des changements d'état
  - ✅ Monitoring du workflow
  - ✅ Outils d'administration

---

#### **src/Entity/ - Entités Doctrine**

**Rôle :** Modèle de données et mapping ORM

**Fichiers :**

##### `Sortie.php`
- **Fonction :** Entité principale représentant une sortie
- **Propriétés :**
  - ✅ Informations de base (nom, description, photo)
  - ✅ Dates (début, limite d'inscription, durée)
  - ✅ Capacité (nombre maximum de participants)
  - ✅ Relations (organisateur, lieu, état, inscriptions)
- **Méthodes métier :**
  - ✅ `getNbInscriptionsActuelles()` : Compte les inscriptions
  - ✅ `isComplete()` : Vérifie si la sortie est complète
  - ✅ `isInscriptionOuverte()` : Vérifie si les inscriptions sont ouvertes
- **Validation :**
  - ✅ Contraintes de dates (début dans le futur)
  - ✅ Contraintes de capacité (minimum 1 participant)
  - ✅ Index de performance sur les colonnes fréquentes

##### `Participant.php`
- **Fonction :** Entité utilisateur avec authentification Symfony
- **Propriétés :**
  - ✅ Informations personnelles (nom, prénom, pseudo, email, téléphone)
  - ✅ Authentification (mot de passe, rôles)
  - ✅ Statut (actif/inactif, administrateur)
  - ✅ Association à un site ENI
- **Interfaces :**
  - ✅ `UserInterface` : Authentification Symfony
  - ✅ `PasswordAuthenticatedUserInterface` : Gestion des mots de passe
- **Sécurité :**
  - ✅ Unicité du pseudo et de l'email
  - ✅ Hachage sécurisé des mots de passe
  - ✅ Gestion des rôles (ROLE_USER, ROLE_ADMIN)

##### `Lieu.php`
- **Fonction :** Représentation géographique des lieux
- **Propriétés :**
  - ✅ Informations géographiques (nom, rue, coordonnées GPS)
  - ✅ Association à une ville
  - ✅ Relations avec les sorties
- **Validation :**
  - ✅ Coordonnées GPS valides
  - ✅ Adresse obligatoire

##### `Ville.php`
- **Fonction :** Entité géographique des villes
- **Propriétés :**
  - ✅ Nom et code postal
  - ✅ Relations avec les lieux
- **Validation :**
  - ✅ Code postal français valide

##### `Site.php`
- **Fonction :** Sites ENI (Nantes, Rennes, etc.)
- **Propriétés :**
  - ✅ Nom du site
  - ✅ Relations avec les participants
- **Utilisation :**
  - ✅ Association des participants à leur site
  - ✅ Filtrage des sorties par site

##### `Etat.php`
- **Fonction :** États des sorties dans le workflow
- **Propriétés :**
  - ✅ Libellé de l'état
  - ✅ Relations avec les sorties
- **États disponibles :**
  - ✅ Créée, Ouverte, Clôturée, En cours, Terminée, Annulée, Historisée

##### `Inscription.php`
- **Fonction :** Liaison participant/sortie
- **Propriétés :**
  - ✅ Date d'inscription
  - ✅ Relations participant et sortie
- **Contraintes :**
  - ✅ Unicité participant/sortie
  - ✅ Date d'inscription automatique

---

#### **src/Form/ - Formulaires Symfony**

**Rôle :** Gestion des formulaires et validation des données

**Fichiers :**

##### `SortieType.php`
- **Fonction :** Formulaire de création/modification des sorties
- **Champs :**
  - ✅ Nom de la sortie
  - ✅ Date et heure de début
  - ✅ Durée (optionnelle)
  - ✅ Date limite d'inscription
  - ✅ Nombre maximum de participants
  - ✅ Description détaillée
  - ✅ Sélection du lieu (avec carte)
  - ✅ URL de la photo
- **Validation :**
  - ✅ Contraintes de dates cohérentes
  - ✅ Validation des URLs d'images
  - ✅ Placeholders informatifs

##### `LieuType.php`
- **Fonction :** Formulaire de création/modification des lieux
- **Champs :**
  - ✅ Nom du lieu
  - ✅ Adresse complète
  - ✅ Coordonnées GPS (latitude/longitude)
  - ✅ Sélection de la ville
- **Validation :**
  - ✅ Coordonnées GPS valides
  - ✅ Adresse obligatoire

##### `ProfilType.php`
- **Fonction :** Formulaire de modification du profil utilisateur
- **Champs :**
  - ✅ Informations personnelles
  - ✅ Téléphone
  - ✅ Email
  - ✅ Sélection du site
- **Validation :**
  - ✅ Unicité de l'email
  - ✅ Format de téléphone français

##### `AdminParticipantType.php`
- **Fonction :** Formulaire d'administration des participants
- **Champs :**
  - ✅ Toutes les informations du participant
  - ✅ Gestion des mots de passe
  - ✅ Statut administrateur
  - ✅ Statut actif/inactif
- **Validation :**
  - ✅ Contraintes d'unicité
  - ✅ Validation des mots de passe

##### `AnnulationType.php`
- **Fonction :** Formulaire d'annulation de sortie
- **Champs :**
  - ✅ Motif d'annulation
- **Validation :**
  - ✅ Motif obligatoire

##### `ChangePasswordType.php`
- **Fonction :** Formulaire de changement de mot de passe
- **Champs :**
  - ✅ Mot de passe actuel
  - ✅ Nouveau mot de passe
  - ✅ Confirmation du nouveau mot de passe
- **Validation :**
  - ✅ Vérification du mot de passe actuel
  - ✅ Correspondance des nouveaux mots de passe

##### `SiteType.php`
- **Fonction :** Formulaire de gestion des sites ENI
- **Champs :**
  - ✅ Nom du site
- **Validation :**
  - ✅ Nom obligatoire et unique

##### `VilleType.php`
- **Fonction :** Formulaire de gestion des villes
- **Champs :**
  - ✅ Nom de la ville
  - ✅ Code postal
- **Validation :**
  - ✅ Code postal français valide

---

#### **src/Repository/ - Repositories Doctrine**

**Rôle :** Accès aux données et requêtes personnalisées

**Fichiers :**

##### `SortieRepository.php`
- **Fonction :** Requêtes personnalisées pour les sorties
- **Méthodes :**
  - ✅ `findWithFilters()` : Recherche avec filtres multiples
  - ✅ `findBySite()` : Filtrage par site ENI
  - ✅ `findByOrganisateur()` : Sorties d'un organisateur
  - ✅ `findByEtat()` : Filtrage par état
  - ✅ `findByDateRange()` : Filtrage par période
- **Optimisations :**
  - ✅ Requêtes optimisées avec QueryBuilder
  - ✅ Jointures pour éviter les requêtes N+1
  - ✅ Index de base de données

##### `ParticipantRepository.php`
- **Fonction :** Requêtes personnalisées pour les participants
- **Méthodes :**
  - ✅ `findBySite()` : Participants d'un site
  - ✅ `findActifs()` : Participants actifs uniquement
  - ✅ `findAdministrateurs()` : Liste des administrateurs
  - ✅ `findByPseudoOrEmail()` : Recherche flexible
- **Sécurité :**
  - ✅ Filtrage des utilisateurs inactifs
  - ✅ Validation des données d'entrée

##### `LieuRepository.php`
- **Fonction :** Requêtes géographiques
- **Méthodes :**
  - ✅ `findByVille()` : Lieux d'une ville
  - ✅ `findByCoordinates()` : Recherche par coordonnées
  - ✅ `findNearby()` : Lieux à proximité
- **Géolocalisation :**
  - ✅ Calculs de distance
  - ✅ Filtrage géographique

##### `VilleRepository.php`
- **Fonction :** Requêtes géographiques des villes
- **Méthodes :**
  - ✅ `findByCodePostal()` : Recherche par code postal
  - ✅ `findByNom()` : Recherche par nom
  - ✅ `findAllOrdered()` : Liste ordonnée
- **Optimisations :**
  - ✅ Cache des résultats fréquents
  - ✅ Index sur les colonnes de recherche

##### `SiteRepository.php`
- **Fonction :** Requêtes pour les sites ENI
- **Méthodes :**
  - ✅ `findAllOrdered()` : Sites ordonnés
  - ✅ `findWithParticipants()` : Sites avec participants
- **Utilisation :**
  - ✅ Filtrage des données par site
  - ✅ Statistiques par site

##### `EtatRepository.php`
- **Fonction :** Requêtes pour les états de workflow
- **Méthodes :**
  - ✅ `findByLibelle()` : Recherche par libellé
  - ✅ `findAllOrdered()` : États ordonnés
- **Workflow :**
  - ✅ Support du système de workflow
  - ✅ Transitions d'état

---

#### **src/Security/ - Sécurité et autorisation**

**Rôle :** Gestion de la sécurité et des permissions

**Fichiers :**

##### `UserChecker.php`
- **Fonction :** Validation des utilisateurs lors de l'authentification
- **Vérifications :**
  - ✅ Utilisateur actif
  - ✅ Compte non verrouillé
  - ✅ Validation des permissions
- **Sécurité :**
  - ✅ Protection contre les comptes inactifs
  - ✅ Validation des rôles

##### `Voter/SortieVoter.php`
- **Fonction :** Autorisation fine sur les sorties
- **Permissions :**
  - ✅ `EDIT` : Modification d'une sortie
  - ✅ `DELETE` : Suppression d'une sortie
  - ✅ `CANCEL` : Annulation d'une sortie
- **Règles :**
  - ✅ Seul l'organisateur ou un admin peut modifier
  - ✅ Suppression uniquement en état "Créée"
  - ✅ Annulation selon l'état et les dates

##### `Voter/LieuVoter.php`
- **Fonction :** Autorisation sur les lieux
- **Permissions :**
  - ✅ `EDIT` : Modification d'un lieu
  - ✅ `DELETE` : Suppression d'un lieu
- **Règles :**
  - ✅ Administrateurs uniquement
  - ✅ Vérification des sorties associées

##### `Voter/ProfilVoter.php`
- **Fonction :** Autorisation sur les profils
- **Permissions :**
  - ✅ `EDIT` : Modification du profil
  - ✅ `VIEW` : Consultation du profil
- **Règles :**
  - ✅ Utilisateur peut modifier son propre profil
  - ✅ Administrateurs peuvent tout voir

---

#### **src/Service/ - Services métier**

**Rôle :** Logique métier et services applicatifs

**Fichiers :**

##### `SortieStateService.php`
- **Fonction :** Gestion des transitions d'état des sorties
- **Méthodes :**
  - ✅ `publierSortie()` : Créée → Ouverte
  - ✅ `cloturerInscriptions()` : Ouverte → Clôturée
  - ✅ `demarrerSortie()` : Clôturée → En cours
  - ✅ `terminerSortie()` : En cours → Terminée
  - ✅ `annulerSortie()` : Annulation selon l'état
  - ✅ `historiserSortie()` : Terminée → Historisée
- **Intégration :**
  - ✅ Symfony Workflow Component
  - ✅ Validation des conditions métier
  - ✅ Synchronisation avec la base de données

##### `InscriptionService.php`
- **Fonction :** Gestion des inscriptions aux sorties
- **Méthodes :**
  - ✅ `inscrire()` : Inscription d'un participant
  - ✅ `desister()` : Désinscription
  - ✅ `getParticipantsInscrits()` : Liste des inscrits
  - ✅ `isInscrit()` : Vérification d'inscription
  - ✅ `annulerToutesInscriptions()` : Annulation en masse
- **Validation :**
  - ✅ Contraintes de capacité
  - ✅ Dates d'inscription
  - ✅ Unicité des inscriptions

##### `SortieWorkflowService.php`
- **Fonction :** Service de workflow avancé
- **Fonctionnalités :**
  - ✅ Gestion des transitions complexes
  - ✅ Validation des règles métier
  - ✅ Notifications automatiques
  - ✅ Logs des transitions
- **Intégration :**
  - ✅ Symfony Workflow
  - ✅ Services de notification
  - ✅ Système de logs

---

#### **src/DataFixtures/ - Données de test**

**Rôle :** Génération de données de test et de démonstration

**Fichiers :**

##### `MainFixtures.php`
- **Fonction :** Fixtures principales du projet
- **Données créées :**
  - ✅ États de workflow (7 états)
  - ✅ Sites ENI (6 sites)
  - ✅ Villes françaises (9 villes)
  - ✅ Lieux touristiques (15 lieux)
  - ✅ Participants (4 utilisateurs)
  - ✅ Sorties d'exemple (3 sorties)
  - ✅ Inscriptions de test
- **Caractéristiques :**
  - ✅ Données réalistes et cohérentes
  - ✅ Images Unsplash pour les sorties
  - ✅ Coordonnées GPS réelles
  - ✅ Dates cohérentes

##### `SortieFixtures.php`
- **Fonction :** Fixtures spécialisées pour les sorties
- **Données :**
  - ✅ Sorties dans différents états
  - ✅ Variété d'activités (culture, sport, nature)
  - ✅ Images représentatives
  - ✅ Descriptions détaillées
- **Utilisation :**
  - ✅ Tests de workflow
  - ✅ Démonstration des fonctionnalités
  - ✅ Données de développement

##### `ParticipantFixtures.php`
- **Fonction :** Fixtures pour les participants
- **Données :**
  - ✅ Utilisateurs avec différents rôles
  - ✅ Répartition sur différents sites
  - ✅ Données personnelles réalistes
- **Sécurité :**
  - ✅ Mots de passe hachés
  - ✅ Emails valides

##### `LieuFixtures.php`
- **Fonction :** Fixtures géographiques
- **Données :**
  - ✅ Lieux touristiques français
  - ✅ Coordonnées GPS précises
  - ✅ Adresses complètes
- **Géolocalisation :**
  - ✅ Support des cartes
  - ✅ Tests de géolocalisation

##### `VilleFixtures.php`
- **Fonction :** Fixtures des villes
- **Données :**
  - ✅ Villes françaises principales
  - ✅ Codes postaux valides
  - ✅ Répartition géographique

##### `SiteFixtures.php`
- **Fonction :** Fixtures des sites ENI
- **Données :**
  - ✅ Sites ENI existants
  - ✅ Noms officiels
  - ✅ Répartition géographique

##### `EtatFixtures.php`
- **Fonction :** Fixtures des états de workflow
- **Données :**
  - ✅ Tous les états du workflow
  - ✅ Libellés cohérents
  - ✅ Support des transitions

##### `InscriptionFixtures.php`
- **Fonction :** Fixtures des inscriptions
- **Données :**
  - ✅ Inscriptions réalistes
  - ✅ Dates cohérentes
  - ✅ Répartition équilibrée

---

## 📁 **templates/ - Templates Twig**

### 🎯 **Rôle**
Interface utilisateur et présentation des données

### 📂 **Structure détaillée**

#### **templates/base.html.twig**
- **Fonction :** Template de base de l'application
- **Composants :**
  - ✅ Navigation responsive avec Bootstrap
  - ✅ Menu utilisateur avec photo de profil
  - ✅ Gestion des rôles (admin/utilisateur)
  - ✅ Messages flash (succès, erreur, info)
  - ✅ Intégration des assets (CSS, JS)
  - ✅ Support des cartes (Leaflet)
- **Responsive :**
  - ✅ Design mobile-first
  - ✅ Navigation adaptative
  - ✅ Interface tactile

#### **templates/home/ - Pages d'accueil**
- **Fichiers :**
  - ✅ `index.html.twig` : Page d'accueil avec liste des sorties
- **Fonctionnalités :**
  - ✅ Filtres de recherche
  - ✅ Affichage des sorties
  - ✅ Pagination
  - ✅ Interface responsive

#### **templates/sortie/ - Pages des sorties**
- **Fichiers :**
  - ✅ `index.html.twig` : Liste des sorties
  - ✅ `show.html.twig` : Détail d'une sortie
  - ✅ `new.html.twig` : Création de sortie
  - ✅ `edit.html.twig` : Modification de sortie
  - ✅ `list.html.twig` : Liste avec filtres
- **Fonctionnalités :**
  - ✅ Affichage des informations détaillées
  - ✅ Gestion des inscriptions
  - ✅ Actions selon les permissions
  - ✅ Intégration cartographique

#### **templates/profil/ - Pages de profil**
- **Fichiers :**
  - ✅ `show.html.twig` : Affichage du profil
  - ✅ `edit.html.twig` : Modification du profil
  - ✅ `change_password.html.twig` : Changement de mot de passe
  - ✅ `photo.html.twig` : Gestion de la photo
- **Fonctionnalités :**
  - ✅ Modification des informations personnelles
  - ✅ Upload de photos
  - ✅ Changement de mot de passe sécurisé

#### **templates/admin/ - Interface d'administration**
- **Fichiers :**
  - ✅ `dashboard.html.twig` : Tableau de bord admin
  - ✅ `participants.html.twig` : Gestion des participants
  - ✅ `sites.html.twig` : Gestion des sites
  - ✅ `villes.html.twig` : Gestion des villes
  - ✅ `lieux.html.twig` : Gestion des lieux
  - ✅ `sorties.html.twig` : Monitoring des sorties
  - ✅ `statistics.html.twig` : Statistiques
  - ✅ `settings.html.twig` : Configuration
- **Fonctionnalités :**
  - ✅ Interface d'administration complète
  - ✅ Gestion des utilisateurs
  - ✅ Monitoring des sorties
  - ✅ Statistiques détaillées

#### **templates/security/ - Pages de sécurité**
- **Fichiers :**
  - ✅ `login.html.twig` : Page de connexion
- **Fonctionnalités :**
  - ✅ Formulaire de connexion sécurisé
  - ✅ Gestion des erreurs
  - ✅ Design cohérent

#### **templates/form/ - Formulaires réutilisables**
- **Fichiers :**
  - ✅ `form_errors.html.twig` : Affichage des erreurs
  - ✅ `form_help.html.twig` : Aide contextuelle
  - ✅ `form_widget.html.twig` : Widgets personnalisés
- **Fonctionnalités :**
  - ✅ Affichage uniforme des erreurs
  - ✅ Aide contextuelle
  - ✅ Widgets personnalisés

#### **templates/mobile/ - Interface mobile**
- **Fichiers :**
  - ✅ `sortie_list.html.twig` : Liste mobile
  - ✅ `sortie_detail.html.twig` : Détail mobile
  - ✅ `profil_mobile.html.twig` : Profil mobile
  - ✅ `navigation_mobile.html.twig` : Navigation mobile
- **Fonctionnalités :**
  - ✅ Interface optimisée mobile
  - ✅ Navigation tactile
  - ✅ Affichage adaptatif

#### **templates/workflow/ - Interface de workflow**
- **Fichiers :**
  - ✅ `transitions.html.twig` : Gestion des transitions
  - ✅ `monitoring.html.twig` : Monitoring du workflow
- **Fonctionnalités :**
  - ✅ Interface de gestion des transitions
  - ✅ Monitoring en temps réel
  - ✅ Outils d'administration

#### **templates/cron/ - Interface des tâches automatiques**
- **Fichiers :**
  - ✅ `dashboard.html.twig` : Tableau de bord cron
  - ✅ `logs.html.twig` : Logs des tâches
- **Fonctionnalités :**
  - ✅ Monitoring des tâches automatiques
  - ✅ Consultation des logs
  - ✅ Simulation des tâches

#### **templates/events_map/ - Carte des événements**
- **Fichiers :**
  - ✅ `index.html.twig` : Carte interactive
- **Fonctionnalités :**
  - ✅ Affichage des sorties sur carte
  - ✅ Filtrage géographique
  - ✅ Informations détaillées

---

## 📁 **config/ - Configuration Symfony**

### 🎯 **Rôle**
Configuration de l'application Symfony et de ses composants

### 📂 **Structure détaillée**

#### **config/packages/ - Configuration des bundles**

##### `framework.yaml`
- **Fonction :** Configuration du framework Symfony
- **Paramètres :**
  - ✅ Configuration de session
  - ✅ Protection CSRF
  - ✅ Gestion des erreurs
  - ✅ Configuration de cache

##### `doctrine.yaml`
- **Fonction :** Configuration de Doctrine ORM
- **Paramètres :**
  - ✅ Configuration de base de données
  - ✅ Mapping des entités
  - ✅ Configuration des migrations
  - ✅ Optimisations de performance

##### `security.yaml`
- **Fonction :** Configuration de sécurité
- **Paramètres :**
  - ✅ Authentification (form_login)
  - ✅ Autorisation (rôles, voters)
  - ✅ Hachage des mots de passe
  - ✅ Protection des routes

##### `workflow.yaml`
- **Fonction :** Configuration du workflow des sorties
- **Paramètres :**
  - ✅ États des sorties
  - ✅ Transitions autorisées
  - ✅ Guards de sécurité
  - ✅ Audit trail

##### `twig.yaml`
- **Fonction :** Configuration du moteur de templates
- **Paramètres :**
  - ✅ Chemins des templates
  - ✅ Configuration de cache
  - ✅ Extensions personnalisées

##### `monolog.yaml`
- **Fonction :** Configuration des logs
- **Paramètres :**
  - ✅ Niveaux de log
  - ✅ Canaux spécialisés
  - ✅ Rotation des fichiers

##### `mailer.yaml`
- **Fonction :** Configuration de l'envoi d'emails
- **Paramètres :**
  - ✅ Serveur SMTP
  - ✅ Configuration des transports
  - ✅ Templates d'emails

##### `asset_mapper.yaml`
- **Fonction :** Configuration des assets
- **Paramètres :**
  - ✅ Gestion des CSS/JS
  - ✅ Optimisation des assets
  - ✅ Intégration Stimulus

##### `ux_turbo.yaml`
- **Fonction :** Configuration de Turbo
- **Paramètres :**
  - ✅ Navigation rapide
  - ✅ Mise à jour partielle
  - ✅ Optimisation des performances

##### `stimulus.yaml`
- **Fonction :** Configuration de Stimulus
- **Paramètres :**
  - ✅ Contrôleurs JavaScript
  - ✅ Intégration avec les templates
  - ✅ Gestion des événements

#### **config/routes/ - Configuration des routes**

##### `framework.yaml`
- **Fonction :** Routes du framework
- **Paramètres :**
  - ✅ Routes de debug
  - ✅ Routes de profiler
  - ✅ Routes de cache

##### `security.yaml`
- **Fonction :** Routes de sécurité
- **Paramètres :**
  - ✅ Routes de connexion/déconnexion
  - ✅ Protection des routes
  - ✅ Redirections

##### `web_profiler.yaml`
- **Fonction :** Routes du profiler web
- **Paramètres :**
  - ✅ Interface de debug
  - ✅ Analyse de performance
  - ✅ Outils de développement

#### **config/services.yaml**
- **Fonction :** Configuration des services
- **Paramètres :**
  - ✅ Injection de dépendances
  - ✅ Configuration des services
  - ✅ Paramètres personnalisés

#### **config/bundles.php`
- **Fonction :** Activation des bundles
- **Paramètres :**
  - ✅ Bundles activés
  - ✅ Configuration par environnement
  - ✅ Bundles de développement

---

## 📁 **migrations/ - Migrations de base de données**

### 🎯 **Rôle**
Évolution du schéma de base de données

### 📂 **Fichiers**

##### `Version20241201000000.php`
- **Fonction :** Migration initiale
- **Contenu :**
  - ✅ Création des tables principales
  - ✅ Index de performance
  - ✅ Contraintes d'intégrité

##### `Version20241201000001.php`
- **Fonction :** Migration des relations
- **Contenu :**
  - ✅ Clés étrangères
  - ✅ Contraintes d'unicité
  - ✅ Index sur les relations

##### `Version20250925095940.php`
- **Fonction :** Migration des optimisations
- **Contenu :**
  - ✅ Index supplémentaires
  - ✅ Optimisations de performance
  - ✅ Contraintes métier

---

## 📁 **public/ - Fichiers publics**

### 🎯 **Rôle**
Point d'entrée web et fichiers statiques

### 📂 **Structure**

#### `index.php`
- **Fonction :** Point d'entrée de l'application
- **Contenu :**
  - ✅ Bootstrap de Symfony
  - ✅ Gestion des erreurs
  - ✅ Configuration de l'environnement

#### `uploads/`
- **Fonction :** Stockage des fichiers uploadés
- **Contenu :**
  - ✅ Photos de profil
  - ✅ Images de sorties
  - ✅ Documents

#### `assets/`
- **Fonction :** Assets compilés
- **Contenu :**
  - ✅ CSS compilé
  - ✅ JavaScript compilé
  - ✅ Images optimisées

---

## 📁 **tests/ - Tests (à développer)**

### 🎯 **Rôle**
Tests unitaires et fonctionnels

### 📂 **Structure recommandée**

```
tests/
├── Unit/               # Tests unitaires
│   ├── Entity/         # Tests des entités
│   ├── Service/        # Tests des services
│   └── Repository/     # Tests des repositories
├── Integration/        # Tests d'intégration
│   ├── Controller/     # Tests des contrôleurs
│   └── Workflow/       # Tests du workflow
└── Functional/         # Tests fonctionnels
    ├── Security/       # Tests de sécurité
    └── Workflow/       # Tests complets
```

---

## 📁 **docs/ - Documentation**

### 🎯 **Rôle**
Documentation complète du projet

### 📂 **Fichiers**

#### `README.md`
- **Fonction :** Documentation principale
- **Contenu :**
  - ✅ Vue d'ensemble du projet
  - ✅ Guide d'installation
  - ✅ Instructions d'utilisation
  - ✅ Architecture technique

#### `architecture.md`
- **Fonction :** Documentation architecturale
- **Contenu :**
  - ✅ Patterns utilisés
  - ✅ Structure détaillée
  - ✅ Diagrammes
  - ✅ Bonnes pratiques

#### `api.md`
- **Fonction :** Documentation API
- **Contenu :**
  - ✅ Endpoints disponibles
  - ✅ Formats de données
  - ✅ Codes de retour
  - ✅ Exemples d'utilisation

#### `workflow-sortie.md`
- **Fonction :** Documentation du workflow
- **Contenu :**
  - ✅ États des sorties
  - ✅ Transitions autorisées
  - ✅ Règles métier
  - ✅ Automatisation

#### `installation.md`
- **Fonction :** Guide d'installation
- **Contenu :**
  - ✅ Prérequis
  - ✅ Étapes d'installation
  - ✅ Configuration
  - ✅ Déploiement

#### `utilisation.md`
- **Fonction :** Guide d'utilisation
- **Contenu :**
  - ✅ Guide utilisateur
  - ✅ Guide administrateur
  - ✅ Fonctionnalités
  - ✅ FAQ

#### `formulaires.md`
- **Fonction :** Documentation des formulaires
- **Contenu :**
  - ✅ Types de formulaires
  - ✅ Validation
  - ✅ Personnalisation
  - ✅ Bonnes pratiques

#### `vues-twig.md`
- **Fonction :** Documentation des templates
- **Contenu :**
  - ✅ Structure des templates
  - ✅ Composants réutilisables
  - ✅ Responsive design
  - ✅ Intégration JavaScript

#### `commande-tick.md`
- **Fonction :** Documentation des commandes
- **Contenu :**
  - ✅ Commandes disponibles
  - ✅ Utilisation
  - ✅ Automatisation
  - ✅ Monitoring

#### `cron-setup.md`
- **Fonction :** Configuration des tâches automatiques
- **Contenu :**
  - ✅ Configuration cron
  - ✅ Monitoring
  - ✅ Logs
  - ✅ Maintenance

#### `amelioration-erreurs-validation.md`
- **Fonction :** Documentation des améliorations
- **Contenu :**
  - ✅ Améliorations apportées
  - ✅ Interface utilisateur
  - ✅ Validation
  - ✅ Expérience utilisateur

---

## 📁 **var/ - Cache et logs**

### 🎯 **Rôle**
Stockage temporaire et logs

### 📂 **Structure**

#### `var/cache/`
- **Fonction :** Cache de l'application
- **Contenu :**
  - ✅ Cache de configuration
  - ✅ Cache de templates
  - ✅ Cache de routes
  - ✅ Cache de validation

#### `var/log/`
- **Fonction :** Logs de l'application
- **Contenu :**
  - ✅ Logs d'application
  - ✅ Logs d'erreurs
  - ✅ Logs de sécurité
  - ✅ Logs de workflow

---

## 🎯 **Conclusion**

Cette architecture détaillée montre un projet Symfony **très bien structuré** avec :

### ✅ **Points forts**
- **Architecture MVC** respectée
- **Séparation des responsabilités** claire
- **Sécurité** robuste avec voters
- **Workflow** sophistiqué et automatisé
- **Interface utilisateur** moderne et responsive
- **Documentation** exhaustive
- **Code propre** et maintenable

### 🚀 **Recommandations**
- **Ajouter des tests** (priorité absolue)
- **Optimiser les performances** (cache, requêtes)
- **Implémenter une API REST** pour les applications mobiles
- **Ajouter des notifications** en temps réel
- **Mettre en place un monitoring** avancé

Le projet ENI-Sortir est un **excellent exemple** d'application Symfony moderne et bien architecturée ! 🎉

