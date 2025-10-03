# ENI-Sortir

## 📋 Description

ENI-Sortir est une application web développée avec Symfony 7.3 qui permet aux participants de l'ENI de créer, gérer et s'inscrire à des sorties organisées. L'application implémente un système de workflow complet pour gérer le cycle de vie des sorties, de leur création jusqu'à leur archivage.

## 🚀 Fonctionnalités principales

### 👥 Gestion des utilisateurs
- **Inscription et authentification** : Système de connexion sécurisé avec rôles
- **Profils utilisateurs** : Gestion des informations personnelles et préférences
- **Rôles** : Participants et administrateurs avec permissions différenciées
- **Sites** : Association des participants à des sites ENI

### 🎯 Gestion des sorties
- **Création de sorties** : Interface intuitive pour créer de nouvelles sorties
- **Workflow complet** : Gestion automatique des états (Créée → Ouverte → Clôturée → En cours → Terminée → Historisée)
- **Inscriptions** : Système d'inscription/désinscription avec validation des contraintes
- **Filtrage et recherche** : Outils avancés pour trouver des sorties
- **Géolocalisation** : Intégration de cartes pour la sélection des lieux

### 🗺️ Gestion géographique
- **Villes et lieux** : Base de données géographique complète
- **Coordonnées GPS** : Support des coordonnées latitude/longitude
- **Interface cartographique** : Sélection visuelle des lieux sur carte

### ⚙️ Administration
- **Tableau de bord admin** : Interface d'administration complète
- **Gestion des participants** : Activation/désactivation, réinitialisation des mots de passe
- **Gestion des données de référence** : Villes, sites, lieux
- **Monitoring des sorties** : Vue d'ensemble et actions administratives

### 🔄 Automatisation
- **Tâches cron** : Traitement automatique des transitions d'état
- **Commandes Symfony** : Outils en ligne de commande pour la maintenance
- **Simulation** : Interface de test des transitions automatiques

## 🏗️ Architecture technique

### Stack technologique
- **Backend** : Symfony 7.3 (PHP 8.2+)
- **Base de données** : MySQL/PostgreSQL avec Doctrine ORM
- **Frontend** : Twig, Bootstrap, Stimulus, Turbo
- **Workflow** : Symfony Workflow Component
- **Sécurité** : Symfony Security Bundle
- **Tests** : PHPUnit

### Structure du projet
```
src/
├── Command/           # Commandes console
├── Controller/        # Contrôleurs MVC
├── Entity/           # Entités Doctrine
├── Form/             # Formulaires Symfony
├── Repository/       # Repositories Doctrine
├── Security/         # Configuration sécurité
├── Service/          # Services métier
└── DataFixtures/     # Données de test
```

## 📊 Modèle de données

### Entités principales

#### Participant
- Informations personnelles (nom, prénom, pseudo, email, téléphone)
- Authentification (mot de passe, rôles)
- Statut (actif/inactif)
- Association à un site ENI

#### Sortie
- Informations de base (nom, description, photo)
- Dates (début, limite d'inscription, durée)
- Capacité (nombre maximum de participants)
- Relations (organisateur, lieu, état, inscriptions)

#### Lieu
- Informations géographiques (nom, rue, coordonnées GPS)
- Association à une ville

#### Inscription
- Liaison participant/sortie
- Date d'inscription
- Contraintes d'unicité

### Workflow des sorties

```mermaid
stateDiagram-v2
    [*] --> Créée
    Créée --> Ouverte : publier
    Ouverte --> Clôturée : clore_auto
    Clôturée --> En cours : lancer
    En cours --> Terminée : terminer
    Terminée --> Historisée : archiver
    Ouverte --> Annulée : annuler
    Clôturée --> Annulée : annuler
```

## 🚀 Installation

### Prérequis
- PHP 8.2 ou supérieur
- Composer
- MySQL/PostgreSQL
- Node.js (pour les assets)

### Étapes d'installation

1. **Cloner le projet**
```bash
git clone <repository-url>
cd sortir
```

2. **Installer les dépendances PHP**
```bash
composer install
```

3. **Configurer l'environnement**
```bash
cp .env .env.local
# Éditer .env.local avec vos paramètres de base de données
```

4. **Créer la base de données**
```bash
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
```

5. **Charger les données de test**
```bash
php bin/console doctrine:fixtures:load
```

6. **Créer un administrateur**
```bash
php bin/console app:create-admin
```

7. **Installer les assets**
```bash
npm install
npm run build
```

8. **Démarrer le serveur**
```bash
symfony server:start
# ou
php -S localhost:8000 -t public/
```

## 🔧 Configuration

### Variables d'environnement importantes
```env
DATABASE_URL="mysql://user:password@127.0.0.1:3306/sortir"
APP_ENV=dev
APP_SECRET=your-secret-key
MAILER_DSN=smtp://localhost:1025
```

### Configuration du workflow
Le workflow des sorties est configuré dans `config/packages/workflow.yaml` et peut être personnalisé selon les besoins.

## 📝 Utilisation

### Pour les participants
1. **S'inscrire** : Créer un compte via l'interface d'inscription
2. **Créer une sortie** : Utiliser le formulaire de création avec sélection sur carte
3. **Publier** : Passer la sortie de l'état "Créée" à "Ouverte"
4. **S'inscrire aux sorties** : Participer aux sorties d'autres organisateurs

### Pour les administrateurs
1. **Tableau de bord** : Accès via `/admin`
2. **Gestion des utilisateurs** : Activation, désactivation, réinitialisation
3. **Gestion des données** : Villes, sites, lieux
4. **Monitoring** : Interface de surveillance des transitions automatiques

### Commandes utiles
```bash
# Traitement des transitions automatiques
php bin/console app:sortie:tick

# Simulation des transitions (sans exécution)
php bin/console app:sortie:tick --dry-run

# Création d'un administrateur
php bin/console app:create-admin

# Nettoyage du cache
php bin/console cache:clear
```

## 🧪 Tests

### Exécution des tests
```bash
# Tests unitaires
php bin/phpunit

# Tests avec couverture
php bin/phpunit --coverage-html coverage/
```

### Données de test
Les fixtures incluent :
- Sites ENI (Nantes, Rennes, Niort, etc.)
- Villes et lieux de test
- Participants avec différents rôles
- Sorties dans différents états

## 🔒 Sécurité

### Authentification
- Hachage des mots de passe avec Symfony PasswordHasher
- Sessions sécurisées
- Protection CSRF sur tous les formulaires

### Autorisation
- Système de rôles (ROLE_USER, ROLE_ADMIN)
- Voters personnalisés pour les permissions sur les sorties
- Guards sur les transitions de workflow

### Validation
- Contraintes de validation sur toutes les entités
- Validation des dates et contraintes métier
- Protection contre les injections SQL via Doctrine ORM

## 📈 Performance

### Optimisations
- Index de base de données sur les colonnes fréquemment utilisées
- Requêtes optimisées avec QueryBuilder
- Cache Symfony pour les configurations
- Lazy loading des relations Doctrine

### Monitoring
- Interface de surveillance des transitions automatiques
- Logs détaillés des opérations
- Métriques de performance via Symfony Profiler

## 🚀 Déploiement

### Environnement de production
1. **Configuration** : `APP_ENV=prod`
2. **Cache** : `php bin/console cache:clear --env=prod`
3. **Assets** : `npm run build`
4. **Base de données** : Migrations en production

### Tâches cron recommandées
```bash
# Traitement des transitions automatiques (toutes les heures)
0 * * * * cd /path/to/project && php bin/console app:sortie:tick

# Nettoyage des logs (quotidien)
0 2 * * * find /path/to/project/var/log -name "*.log" -mtime +30 -delete
```

## 🤝 Contribution

### Standards de code
- PSR-12 pour le style de code PHP
- Documentation PHPDoc complète
- Tests unitaires pour les nouvelles fonctionnalités
- Commits conventionnels

### Processus de développement
1. Fork du projet
2. Création d'une branche feature
3. Développement avec tests
4. Pull request avec description détaillée

## 📚 Documentation complémentaire

- [Documentation API](docs/api.md)
- [Architecture détaillée](docs/architecture.md)
- [Guide des formulaires](docs/formulaires.md)
- [Workflow des sorties](docs/workflow-sortie.md)
- [Configuration des tâches cron](docs/cron-setup.md)

## 📄 Licence

Ce projet est développé dans le cadre de la formation ENI et est destiné à un usage éducatif.

## 👥 Équipe

Développé par l'équipe ENI dans le cadre du projet de fin de formation.

---

*Pour toute question ou support, consultez la documentation complète dans le dossier `docs/` ou contactez l'équipe de développement.*

