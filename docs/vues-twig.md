# Vues Twig - Documentation

## Vue d'ensemble

Cette documentation décrit la structure et l'organisation des vues Twig de l'application Sortir, incluant les templates responsive et mobile.

## Structure des Templates

### 1. Template de Base (`templates/base.html.twig`)

**Fonctionnalités** :
- Navigation responsive avec Bootstrap
- Flash messages avec icônes
- Footer
- Support des icônes Bootstrap Icons
- CSS mobile intégré

**Navigation** :
- Accueil (toujours visible)
- Créer une sortie (utilisateurs connectés)
- Mon profil (utilisateurs connectés)
- Administration (administrateurs)
- Menu déroulant utilisateur avec déconnexion

**Flash Messages** :
- Support des types : success, error, warning, info
- Icônes automatiques selon le type
- Bouton de fermeture

### 2. Page d'Accueil (`templates/home/index.html.twig`)

**Fonctionnalités** :
- Liste paginée des sorties
- Filtres par site, état, période
- Cartes de sorties avec informations essentielles
- Actions d'inscription/désinscription
- Design responsive

**Filtres** :
- Site (dropdown)
- État (dropdown)
- Période (date de début)
- Recherche par nom

**Cartes de Sorties** :
- Nom et état avec badge coloré
- Date et heure de début
- Lieu
- Organisateur
- Nombre d'inscrits/maximum
- Description tronquée
- Boutons d'action

### 3. Templates des Sorties

#### `templates/sortie/form.html.twig`
Template réutilisable pour les formulaires de sortie :
- Création et modification
- Tous les champs avec validations
- Design responsive
- Boutons d'action

#### `templates/sortie/new.html.twig`
Hérite de `form.html.twig` :
- Titre : "Créer une Sortie"
- Bouton : "Créer la sortie"
- Annulation vers l'accueil

#### `templates/sortie/edit.html.twig`
Hérite de `form.html.twig` :
- Titre : "Modifier [Nom Sortie]"
- Bouton : "Enregistrer"
- Annulation vers les détails

#### `templates/sortie/show.html.twig`
Affichage détaillé d'une sortie :
- Informations complètes avec icônes
- Actions contextuelles (publier, modifier, annuler, supprimer)
- Liste des participants inscrits
- Inscription/désinscription

### 4. Templates de Profil

#### `templates/profil/show.html.twig`
Affichage du profil utilisateur :
- Informations personnelles avec icônes
- Badge administrateur si applicable
- Actions (modifier, changer mot de passe)

#### `templates/profil/edit.html.twig`
Modification du profil :
- Formulaire avec tous les champs
- Validation côté client
- Boutons d'action

#### `templates/profil/change_password.html.twig`
Changement de mot de passe :
- Ancien mot de passe
- Nouveau mot de passe avec confirmation
- Validation

#### `templates/profil/show_other.html.twig`
Affichage du profil d'un autre utilisateur :
- Informations publiques uniquement
- Badge administrateur
- Bouton retour

### 5. Template de Connexion (`templates/security/login.html.twig`)

**Fonctionnalités** :
- Formulaire de connexion centré
- Gestion des erreurs
- Design responsive
- Icônes pour les champs

### 6. Templates d'Administration

#### `templates/admin/villes.html.twig`
Gestion des villes :
- Formulaire d'ajout
- Liste avec actions (modifier, supprimer)
- Table responsive

#### `templates/admin/sites.html.twig`
Gestion des sites :
- Formulaire d'ajout
- Liste avec actions
- Table responsive

#### `templates/admin/participants.html.twig`
Gestion des participants :
- Liste avec filtres
- Actions (activer, désactiver, réinitialiser MDP)
- Table responsive

#### `templates/admin/sorties.html.twig`
Vue d'ensemble des sorties :
- Liste avec filtres
- Actions d'annulation
- Table responsive

### 7. Templates Mobile

#### `templates/mobile/base.html.twig`
Template de base pour mobile :
- Navigation fixe en bas
- Header fixe en haut
- Design optimisé mobile
- Navigation par onglets

#### `templates/mobile/home.html.twig`
Page d'accueil mobile :
- Filtres simplifiés
- Cartes de sorties adaptées
- Actions d'inscription

#### `templates/mobile/sortie_show.html.twig`
Détails de sortie mobile :
- Informations organisées en cartes
- Actions en boutons pleine largeur
- Liste des participants

#### `templates/mobile/login.html.twig`
Connexion mobile :
- Formulaire centré
- Design adapté mobile

## CSS Mobile (`assets/styles/mobile.css`)

### Classes CSS Spécifiques

#### Navigation
- `.mobile-nav` : Navigation fixe en bas
- `.mobile-header` : Header fixe en haut
- `.mobile-content` : Contenu avec padding pour la navigation

#### Cartes
- `.mobile-card` : Cartes adaptées au mobile
- `.mobile-btn` : Boutons pleine largeur

#### Filtres
- `.filters-mobile` : Filtres adaptés mobile
- `.sortie-card` : Cartes de sorties mobile

#### Détails
- `.sortie-details` : Détails de sortie mobile
- `.actions-mobile` : Actions adaptées mobile

#### Profil
- `.profil-mobile` : Profil adapté mobile

#### Administration
- `.admin-mobile` : Tables d'administration mobile

### Media Queries

```css
@media (max-width: 768px) {
    /* Styles pour mobile */
}
```

### Utilitaires Mobile

- `.mobile-hidden` : Masqué sur mobile
- `.mobile-only` : Visible uniquement sur mobile
- `.desktop-only` : Visible uniquement sur desktop
- `.text-mobile-center` : Texte centré sur mobile
- `.mb-mobile-2` : Marge bottom mobile
- `.p-mobile-2` : Padding mobile

## Icônes Bootstrap Icons

### Navigation
- `bi-house` : Accueil
- `bi-plus-circle` : Créer
- `bi-person` : Profil
- `bi-gear` : Administration
- `bi-box-arrow-in-right` : Connexion
- `bi-box-arrow-right` : Déconnexion

### Actions
- `bi-eye` : Voir
- `bi-pencil` : Modifier
- `bi-check-circle` : Valider
- `bi-x-circle` : Annuler
- `bi-trash` : Supprimer
- `bi-key` : Mot de passe

### Informations
- `bi-calendar-date` : Date
- `bi-clock` : Heure
- `bi-geo-alt` : Lieu
- `bi-people` : Participants
- `bi-person` : Utilisateur
- `bi-building` : Ville/Site
- `bi-envelope` : Email
- `bi-telephone` : Téléphone

### États
- `bi-info-circle` : Information
- `bi-exclamation-triangle` : Attention/Erreur
- `bi-check-circle` : Succès
- `bi-shield-check` : Administrateur

## Responsive Design

### Breakpoints Bootstrap
- `xs` : < 576px (mobile)
- `sm` : ≥ 576px (mobile large)
- `md` : ≥ 768px (tablet)
- `lg` : ≥ 992px (desktop)
- `xl` : ≥ 1200px (desktop large)

### Classes Responsive
- `.col-md-6` : 50% sur tablet et plus
- `.col-lg-4` : 33% sur desktop et plus
- `.d-md-flex` : Flexbox sur tablet et plus
- `.d-lg-none` : Masqué sur desktop

### Navigation Responsive
- Menu hamburger sur mobile
- Menu horizontal sur desktop
- Dropdown pour les actions utilisateur

## Accessibilité

### Attributs ARIA
- `role="alert"` pour les flash messages
- `aria-label` pour les boutons d'action
- `aria-expanded` pour les dropdowns

### Focus
- Styles de focus visibles
- Navigation au clavier
- Contraste des couleurs

### Sémantique
- Structure HTML sémantique
- Labels appropriés
- Titres hiérarchisés

## Performance

### Optimisations
- CSS minifié
- Icônes en font
- Images optimisées
- Lazy loading pour les listes

### Mobile First
- Design mobile en priorité
- Progressive enhancement
- Fallbacks pour les fonctionnalités

## Bonnes Pratiques

### Structure
- Templates réutilisables
- Héritage logique
- Séparation des responsabilités

### Maintenance
- Noms de classes cohérents
- Documentation des composants
- Tests de responsive

### UX
- Navigation intuitive
- Feedback utilisateur
- Actions claires
- Messages d'erreur explicites

## Exemples d'Utilisation

### Template de Base
```twig
{% extends 'base.html.twig' %}

{% block title %}Mon Titre{% endblock %}

{% block main_class %}py-4{% endblock %}

{% block body %}
<div class="container">
    <!-- Contenu -->
</div>
{% endblock %}
```

### Template Mobile
```twig
{% extends 'mobile/base.html.twig' %}

{% block title %}Mon Titre Mobile{% endblock %}

{% block body %}
<div class="container-fluid p-0">
    <div class="mobile-card">
        <!-- Contenu mobile -->
    </div>
</div>
{% endblock %}
```

### Flash Messages
```php
$this->addFlash('success', 'Opération réussie');
$this->addFlash('error', 'Une erreur est survenue');
$this->addFlash('warning', 'Attention');
$this->addFlash('info', 'Information');
```

### Icônes dans les Templates
```twig
<i class="bi bi-calendar-date text-primary"></i>
<i class="bi bi-geo-alt text-primary"></i>
<i class="bi bi-people text-primary"></i>
```

## Tests et Validation

### Tests Responsive
- Test sur différentes tailles d'écran
- Test sur différents navigateurs
- Test sur appareils mobiles réels

### Validation HTML
- Structure HTML valide
- Attributs ARIA appropriés
- Sémantique correcte

### Performance
- Temps de chargement
- Optimisation des images
- Minification CSS/JS
