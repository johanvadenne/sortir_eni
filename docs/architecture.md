# Architecture - ENI-Sortir

## 📋 Vue d'ensemble

ENI-Sortir est une application web moderne construite avec Symfony 7.3, suivant les principes de l'architecture MVC (Model-View-Controller) et les bonnes pratiques de développement PHP. Cette documentation détaille l'architecture technique, les choix de conception et les patterns utilisés.

## 🏗️ Architecture générale

### Pattern architectural
L'application suit le pattern **MVC (Model-View-Controller)** avec des extensions pour la gestion des services métier et des workflows.

```
┌─────────────────┐    ┌─────────────────┐    ┌─────────────────┐
│     View        │    │   Controller    │    │     Model       │
│   (Templates)   │◄──►│  (Controllers)  │◄──►│   (Entities)    │
│                 │    │                 │    │                 │
│   - Twig        │    │   - Routes      │    │   - Doctrine    │
│   - Bootstrap   │    │   - Security    │    │   - Validation  │
│   - Stimulus    │    │   - Services    │    │   - Workflow    │
└─────────────────┘    └─────────────────┘    └─────────────────┘
```

### Couches de l'application

#### 1. Couche Présentation (View)
- **Templates Twig** : Rendu des pages HTML
- **Assets** : CSS, JavaScript, images
- **Responsive Design** : Interface adaptative
- **Stimulus** : Interactions JavaScript modernes

#### 2. Couche Contrôleur (Controller)
- **Contrôleurs Symfony** : Gestion des requêtes HTTP
- **Routes** : Définition des endpoints
- **Sécurité** : Authentification et autorisation
- **Validation** : Contrôle des données d'entrée

#### 3. Couche Métier (Service)
- **Services métier** : Logique applicative
- **Workflow** : Gestion des états des sorties
- **Validation métier** : Règles de gestion

#### 4. Couche Données (Model)
- **Entités Doctrine** : Modèle de données
- **Repositories** : Accès aux données
- **Migrations** : Évolution du schéma
- **Fixtures** : Données de test

## 🗂️ Structure détaillée

### Organisation des dossiers

```
src/
├── Command/              # Commandes console
│   ├── CreateAdminCommand.php
│   └── SortieTickCommand.php
├── Controller/           # Contrôleurs MVC
│   ├── AdminController.php
│   ├── HomeController.php
│   ├── SortieController.php
│   ├── InscriptionController.php
│   ├── LieuController.php
│   ├── ProfilController.php
│   ├── SecurityController.php
│   └── CronController.php
├── Entity/              # Entités Doctrine
│   ├── Sortie.php
│   ├── Participant.php
│   ├── Lieu.php
│   ├── Ville.php
│   ├── Site.php
│   ├── Etat.php
│   └── Inscription.php
├── Form/                # Formulaires Symfony
│   ├── SortieType.php
│   ├── LieuType.php
│   ├── ProfilType.php
│   ├── SiteType.php
│   └── VilleType.php
├── Repository/          # Repositories Doctrine
│   ├── SortieRepository.php
│   ├── ParticipantRepository.php
│   ├── LieuRepository.php
│   └── VilleRepository.php
├── Security/            # Configuration sécurité
│   ├── UserChecker.php
│   └── Voter/
│       ├── SortieVoter.php
│       ├── LieuVoter.php
│       └── ProfilVoter.php
├── Service/             # Services métier
│   ├── SortieWorkflowService.php
│   ├── SortieStateService.php
│   └── InscriptionService.php
└── DataFixtures/        # Données de test
    ├── MainFixtures.php
    ├── ParticipantFixtures.php
    ├── SortieFixtures.php
    └── LieuFixtures.php
```

## 🎯 Patterns de conception

### 1. Pattern Repository
**Utilisation** : Accès aux données via Doctrine
**Avantages** : Abstraction de la couche données, testabilité

```php
class SortieRepository extends ServiceEntityRepository
{
    public function findWithFilters(array $criteria, ...): array
    {
        $qb = $this->createQueryBuilder('s');
        // Logique de filtrage
        return $qb->getQuery()->getResult();
    }
}
```

### 2. Pattern Service
**Utilisation** : Logique métier complexe
**Avantages** : Réutilisabilité, testabilité, séparation des responsabilités

```php
class InscriptionService
{
    public function inscrire(Participant $participant, Sortie $sortie): Inscription
    {
        $this->validateInscription($participant, $sortie);
        // Logique d'inscription
    }
}
```

### 3. Pattern Voter
**Utilisation** : Autorisation fine
**Avantages** : Sécurité granulaire, maintenabilité

```php
class SortieVoter extends Voter
{
    protected function supports(string $attribute, $subject): bool
    {
        return $subject instanceof Sortie && in_array($attribute, ['EDIT', 'DELETE']);
    }
}
```

### 4. Pattern Workflow
**Utilisation** : Gestion des états des sorties
**Avantages** : Automatisation, traçabilité, règles métier centralisées

```yaml
framework:
    workflows:
        sortie_workflow:
            type: 'state_machine'
            places: [Créée, Ouverte, Clôturée, En cours, Terminée, Annulée, Historisée]
            transitions:
                publier: { from: Créée, to: Ouverte }
```

## 🔄 Workflow des sorties

### Machine à états
Le workflow des sorties est implémenté avec Symfony Workflow Component :

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

### Transitions automatiques
- **clore_auto** : Clôture automatique des inscriptions
- **lancer** : Démarrage automatique de la sortie
- **terminer** : Fin automatique basée sur la durée
- **archiver** : Archivage après 1 mois

### Guards de sécurité
Chaque transition est protégée par des guards :
```php
guard: 'is_granted("ROLE_USER") and subject.getOrganisateur() == user or is_granted("ROLE_ADMIN")'
```

## 🗄️ Modèle de données

### Diagramme entité-relation

```mermaid
erDiagram
    PARTICIPANT ||--o{ SORTIE : organise
    PARTICIPANT ||--o{ INSCRIPTION : participe
    PARTICIPANT }o--|| SITE : appartient

    SORTIE ||--o{ INSCRIPTION : contient
    SORTIE }o--|| LIEU : se_déroule
    SORTIE }o--|| ETAT : a_état
    SORTIE }o--|| PARTICIPANT : organisateur

    LIEU }o--|| VILLE : situé_dans

    PARTICIPANT {
        int id PK
        string pseudo UK
        string nom
        string prenom
        string telephone
        string mail UK
        string motDePasse
        bool administrateur
        bool actif
        int site_id FK
    }

    SORTIE {
        int id PK
        string nom
        datetime dateHeureDebut
        int duree
        datetime dateLimiteInscription
        int nbInscriptionsMax
        string infosSortie
        string urlPhoto
        int etat_id FK
        int lieu_id FK
        int organisateur_id FK
    }

    INSCRIPTION {
        int id PK
        datetime dateInscription
        int sortie_id FK
        int participant_id FK
    }

    LIEU {
        int id PK
        string nom
        string rue
        float latitude
        float longitude
        int ville_id FK
    }

    VILLE {
        int id PK
        string nom
        string codePostal
    }

    SITE {
        int id PK
        string nom
    }

    ETAT {
        int id PK
        string libelle
    }
```

### Relations et contraintes

#### Relations principales
- **Participant ↔ Sortie** : Un participant peut organiser plusieurs sorties
- **Participant ↔ Inscription** : Un participant peut s'inscrire à plusieurs sorties
- **Sortie ↔ Inscription** : Une sortie peut avoir plusieurs inscriptions
- **Sortie ↔ Lieu** : Une sortie se déroule dans un lieu unique
- **Lieu ↔ Ville** : Un lieu appartient à une ville unique

#### Contraintes d'intégrité
- **Unicité** : Pseudo et email uniques par participant
- **Unicité** : Une inscription unique par participant/sortie
- **Référentielle** : Toutes les clés étrangères sont validées
- **Métier** : Date limite < Date début, Nombre max > 0

## 🔒 Architecture de sécurité

### Authentification
```php
// Configuration dans security.yaml
security:
    providers:
        app_user_provider:
            entity:
                class: App\Entity\Participant
                property: pseudo

    firewalls:
        main:
            form_login:
                login_path: login
                check_path: login
            logout:
                path: logout
```

### Autorisation
#### Rôles
- `ROLE_USER` : Utilisateur standard
- `ROLE_ADMIN` : Administrateur

#### Permissions granulaires
- `EDIT` : Modifier une sortie
- `DELETE` : Supprimer une sortie
- `PUBLISH` : Publier une sortie
- `CANCEL` : Annuler une sortie

### Protection CSRF
Tous les formulaires sont protégés contre les attaques CSRF :
```php
#[Route('/sorties/{id}/publier', methods: ['POST'])]
public function publier(Sortie $sortie): Response
{
    // Protection CSRF automatique
}
```

## 🚀 Performance et optimisation

### Optimisations base de données
```php
// Index sur les colonnes fréquemment utilisées
#[ORM\Index(name: 'idx_sortie_etat', columns: ['etat_id'])]
#[ORM\Index(name: 'idx_sortie_date_debut', columns: ['date_heure_debut'])]
#[ORM\Index(name: 'idx_sortie_date_limite', columns: ['date_limite_inscription'])]
```

### Requêtes optimisées
```php
// Utilisation du QueryBuilder pour des requêtes complexes
public function findWithFilters(array $criteria, ...): array
{
    $qb = $this->createQueryBuilder('s')
        ->join('s.etat', 'e')
        ->join('s.lieu', 'l')
        ->join('l.ville', 'v');

    // Filtres dynamiques
    if (isset($criteria['site'])) {
        $qb->andWhere('s.organisateur IN (SELECT p FROM App\Entity\Participant p WHERE p.site = :site)')
           ->setParameter('site', $criteria['site']);
    }

    return $qb->getQuery()->getResult();
}
```

### Cache Symfony
- **Cache de configuration** : Optimisation du chargement
- **Cache de routes** : Performance du routage
- **Cache de validation** : Réutilisation des métadonnées

## 🧪 Architecture de test

### Structure des tests
```
tests/
├── Unit/               # Tests unitaires
│   ├── Entity/
│   ├── Service/
│   └── Repository/
├── Integration/        # Tests d'intégration
│   ├── Controller/
│   └── Workflow/
└── Functional/         # Tests fonctionnels
    ├── Security/
    └── Workflow/
```

### Types de tests
1. **Tests unitaires** : Services, entités, repositories
2. **Tests d'intégration** : Contrôleurs, workflow
3. **Tests fonctionnels** : Scénarios complets
4. **Tests de sécurité** : Authentification, autorisation

## 🔄 Architecture des services

### Services métier

#### SortieStateService
**Responsabilité** : Gestion des transitions d'état des sorties
```php
class SortieStateService
{
    public function publierSortie(Sortie $sortie): bool
    public function cloturerInscriptions(Sortie $sortie): bool
    public function demarrerSortie(Sortie $sortie): bool
    public function terminerSortie(Sortie $sortie): bool
    public function annulerSortie(Sortie $sortie): bool
    public function historiserSortie(Sortie $sortie): bool
}
```

#### InscriptionService
**Responsabilité** : Gestion des inscriptions aux sorties
```php
class InscriptionService
{
    public function inscrire(Participant $participant, Sortie $sortie): Inscription
    public function desister(Participant $participant, Sortie $sortie): bool
    public function getParticipantsInscrits(Sortie $sortie): array
    public function isInscrit(Participant $participant, Sortie $sortie): bool
}
```

### Injection de dépendances
```php
class SortieController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private SortieStateService $sortieStateService,
        private InscriptionService $inscriptionService
    ) {
    }
}
```

## 📱 Architecture frontend

### Stack frontend
- **Twig** : Moteur de templates
- **Bootstrap 5** : Framework CSS
- **Stimulus** : Framework JavaScript léger
- **Turbo** : Navigation rapide
- **Asset Mapper** : Gestion des assets

### Structure des templates
```
templates/
├── base.html.twig          # Template de base
├── home/                   # Pages d'accueil
├── sortie/                 # Pages des sorties
├── profil/                 # Pages de profil
├── admin/                  # Interface d'administration
├── security/               # Pages de sécurité
├── form/                   # Formulaires
└── mobile/                 # Interface mobile
```

### Responsive design
- **Mobile-first** : Conception adaptative
- **Breakpoints Bootstrap** : xs, sm, md, lg, xl
- **Interface mobile** : Templates dédiés

## 🔧 Architecture de déploiement

### Environnements
1. **Development** : Environnement de développement local
2. **Testing** : Environnement de tests automatisés
3. **Staging** : Environnement de pré-production
4. **Production** : Environnement de production

### Configuration par environnement
```yaml
# config/packages/framework.yaml
framework:
    secret: '%env(APP_SECRET)%'
    csrf_protection: true
    session:
        handler_id: null
        cookie_secure: auto
        cookie_samesite: lax
```

### Variables d'environnement
```env
# .env.local
APP_ENV=prod
APP_SECRET=your-secret-key
DATABASE_URL="mysql://user:password@localhost:3306/sortir"
MAILER_DSN=smtp://localhost:1025
```

## 📊 Monitoring et observabilité

### Logs
- **Monolog** : Système de logs intégré
- **Niveaux** : DEBUG, INFO, WARNING, ERROR, CRITICAL
- **Channels** : app, security, workflow

### Métriques
- **Performance** : Temps de réponse, requêtes DB
- **Utilisation** : Nombre de sorties, inscriptions
- **Erreurs** : Taux d'erreur, exceptions

### Health checks
```php
#[Route('/health', name: 'health_check')]
public function healthCheck(): Response
{
    // Vérification de la base de données
    // Vérification des services externes
    return new Response('OK', 200);
}
```

## 🔮 Évolutions futures

### Améliorations prévues
1. **API REST** : Endpoints JSON pour applications mobiles
2. **Webhooks** : Notifications en temps réel
3. **Cache Redis** : Amélioration des performances
4. **Queue** : Traitement asynchrone des tâches
5. **Microservices** : Découplage des fonctionnalités

### Scalabilité
- **Load balancing** : Répartition de charge
- **Database sharding** : Partitionnement des données
- **CDN** : Distribution des assets statiques
- **Caching** : Mise en cache des données fréquentes

---

*Cette documentation d'architecture est maintenue à jour avec l'évolution du projet. Pour toute question ou suggestion d'amélioration, contactez l'équipe de développement.*

