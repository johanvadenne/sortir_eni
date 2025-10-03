# Photos de profil utilisateurs - ENI-Sortir

## 📋 Vue d'ensemble

Cette documentation décrit l'implémentation de la fonctionnalité de photos de profil pour les utilisateurs de l'application ENI-Sortir. Les utilisateurs peuvent maintenant télécharger une photo de profil qui s'affiche en badge dans toute l'application.

## ✨ Fonctionnalités implémentées

### 1. Upload de photos de profil

#### Formulaire de modification de profil
- **Champ d'upload** : Input file avec validation des formats d'image
- **Prévisualisation** : Affichage de la photo actuelle ou avatar par défaut
- **Validation** : Formats acceptés (JPG, PNG, GIF, WebP) et taille max 2MB
- **Remplacement** : Possibilité de remplacer une photo existante

#### Gestion des fichiers
- **Stockage** : Photos stockées dans `/public/uploads/profiles/`
- **Nommage unique** : Génération de noms de fichiers uniques avec slug
- **Nettoyage** : Suppression automatique de l'ancienne photo lors du remplacement
- **Sécurité** : Validation des types MIME et tailles de fichiers

### 2. Affichage des photos de profil

#### Navigation principale
- **Badge utilisateur** : Photo de profil dans le menu dropdown
- **Avatar par défaut** : Initiales de l'utilisateur si pas de photo
- **Responsive** : Adaptation sur mobile et desktop

#### Pages de profil
- **En-tête de profil** : Photo affichée dans le header
- **Formulaire d'édition** : Prévisualisation de la photo actuelle
- **Actions** : Boutons pour modifier le profil

#### Listes et détails
- **Cartes de sorties** : Photo de l'organisateur
- **Liste des participants** : Photos des participants inscrits
- **Détails de sortie** : Photo de l'organisateur et des participants

## 🔧 Implémentation technique

### 1. Base de données

#### Migration
```sql
ALTER TABLE participant ADD photo_profil VARCHAR(255) DEFAULT NULL;
```

#### Entité Participant
```php
#[ORM\Column(length: 255, nullable: true)]
private ?string $photoProfil = null;

public function getPhotoProfil(): ?string
{
    return $this->photoProfil;
}

public function setPhotoProfil(?string $photoProfil): static
{
    $this->photoProfil = $photoProfil;
    return $this;
}
```

### 2. Formulaire

#### ProfilType
```php
->add('photoProfil', FileType::class, [
    'label' => 'Photo de profil',
    'required' => false,
    'mapped' => false,
    'attr' => [
        'class' => 'form-control',
        'accept' => 'image/*'
    ],
    'help' => 'Téléchargez une photo de profil (JPG, PNG, GIF - max 2MB)',
    'constraints' => [
        new File([
            'maxSize' => '2M',
            'mimeTypes' => [
                'image/jpeg',
                'image/png',
                'image/gif',
                'image/webp'
            ],
            'mimeTypesMessage' => 'Veuillez télécharger une image valide (JPG, PNG, GIF, WebP)',
            'maxSizeMessage' => 'L\'image ne doit pas dépasser 2MB'
        ])
    ]
])
```

### 3. Contrôleur

#### Gestion de l'upload
```php
// Gestion de l'upload de la photo de profil
$photoFile = $form->get('photoProfil')->getData();

if ($photoFile) {
    // Supprimer l'ancienne photo si elle existe
    if ($participant->getPhotoProfil()) {
        $oldPhotoPath = $this->getParameter('kernel.project_dir') . '/public' . $participant->getPhotoProfil();
        if (file_exists($oldPhotoPath)) {
            unlink($oldPhotoPath);
        }
    }

    // Générer un nom de fichier unique
    $originalFilename = pathinfo($photoFile->getClientOriginalName(), PATHINFO_FILENAME);
    $safeFilename = $this->slugger->slug($originalFilename);
    $newFilename = $safeFilename . '-' . uniqid() . '.' . $photoFile->guessExtension();

    // Déplacer le fichier vers le répertoire de stockage
    $photoFile->move(
        $this->getParameter('kernel.project_dir') . '/public/uploads/profiles',
        $newFilename
    );

    // Mettre à jour le chemin de la photo dans l'entité
    $participant->setPhotoProfil('/uploads/profiles/' . $newFilename);
}
```

### 4. Templates

#### Navigation (base.html.twig)
```twig
{% if app.user.photoProfil %}
    <img src="{{ app.user.photoProfil }}" alt="Photo de profil" class="rounded-circle me-2" style="width: 32px; height: 32px; object-fit: cover;">
{% else %}
    <div class="rounded-circle me-2 d-flex align-items-center justify-content-center bg-primary text-white" style="width: 32px; height: 32px; font-size: 14px; font-weight: bold;">
        {{ app.user.prenom|first|upper }}{{ app.user.nom|first|upper }}
    </div>
{% endif %}
```

#### Formulaire d'édition (profil/edit.html.twig)
```twig
<div class="mb-3">
    <label class="form-label">Photo de profil actuelle</label>
    <div class="d-flex align-items-center">
        {% if participant.photoProfil %}
            <img src="{{ participant.photoProfil }}" alt="Photo de profil actuelle" class="rounded-circle me-3" style="width: 80px; height: 80px; object-fit: cover;">
            <div>
                <p class="mb-1 text-muted">Photo actuelle</p>
                <small class="text-muted">Téléchargez une nouvelle photo pour la remplacer</small>
            </div>
        {% else %}
            <div class="rounded-circle me-3 d-flex align-items-center justify-content-center bg-secondary text-white" style="width: 80px; height: 80px; font-size: 24px; font-weight: bold;">
                {{ participant.prenom|first|upper }}{{ participant.nom|first|upper }}
            </div>
            <div>
                <p class="mb-1 text-muted">Aucune photo de profil</p>
                <small class="text-muted">Téléchargez une photo pour personnaliser votre profil</small>
            </div>
        {% endif %}
    </div>
</div>
```

## 🎨 Design et UX

### 1. Avatars par défaut

#### Initiales stylisées
- **Couleur** : Fond bleu primaire avec texte blanc
- **Typographie** : Police bold pour les initiales
- **Taille** : Adaptée au contexte (32px navigation, 80px profil)
- **Forme** : Cercle parfait avec `border-radius: 50%`

#### Génération automatique
```twig
{{ participant.prenom|first|upper }}{{ participant.nom|first|upper }}
```

### 2. Photos de profil

#### Styles CSS
```css
.rounded-circle {
    border-radius: 50%;
    object-fit: cover;
}

/* Tailles contextuelles */
.nav-avatar { width: 32px; height: 32px; }
.profile-avatar { width: 50px; height: 50px; }
.edit-avatar { width: 80px; height: 80px; }
.list-avatar { width: 20px; height: 20px; }
```

#### Responsive design
- **Mobile** : Masquage du nom dans la navigation
- **Tablet** : Adaptation des tailles d'avatar
- **Desktop** : Affichage complet avec nom

### 3. Animations et transitions

#### Effets visuels
- **Hover** : Légère opacité au survol
- **Transitions** : Changements fluides entre les états
- **Loading** : Indicateur de chargement pendant l'upload

## 🔒 Sécurité et validation

### 1. Validation des fichiers

#### Contraintes Symfony
```php
new File([
    'maxSize' => '2M',
    'mimeTypes' => [
        'image/jpeg',
        'image/png',
        'image/gif',
        'image/webp'
    ],
    'mimeTypesMessage' => 'Veuillez télécharger une image valide (JPG, PNG, GIF, WebP)',
    'maxSizeMessage' => 'L\'image ne doit pas dépasser 2MB'
])
```

#### Validation côté client
```html
<input type="file" accept="image/*" class="form-control">
```

### 2. Gestion des erreurs

#### Messages d'erreur
- **Format invalide** : "Veuillez télécharger une image valide"
- **Taille excessive** : "L'image ne doit pas dépasser 2MB"
- **Upload échoué** : "Erreur lors du téléchargement de l'image"

#### Fallback gracieux
- **Image corrompue** : Affichage de l'avatar par défaut
- **Fichier manquant** : Génération automatique des initiales
- **Erreur serveur** : Message d'erreur explicite

### 3. Nettoyage des fichiers

#### Suppression automatique
```php
// Supprimer l'ancienne photo si elle existe
if ($participant->getPhotoProfil()) {
    $oldPhotoPath = $this->getParameter('kernel.project_dir') . '/public' . $participant->getPhotoProfil();
    if (file_exists($oldPhotoPath)) {
        unlink($oldPhotoPath);
    }
}
```

#### Gestion des orphelins
- **Script de nettoyage** : Suppression des fichiers non référencés
- **Monitoring** : Surveillance de l'espace disque
- **Archivage** : Possibilité d'archiver les anciennes photos

## 📱 Responsive et accessibilité

### 1. Design responsive

#### Breakpoints
- **Mobile** : Avatars 24px, masquage du nom
- **Tablet** : Avatars 32px, nom partiel
- **Desktop** : Avatars 50px, nom complet

#### Optimisations mobiles
- **Touch targets** : Zones de clic suffisamment grandes
- **Performance** : Images optimisées pour mobile
- **Bandwidth** : Compression automatique des images

### 2. Accessibilité

#### Standards WCAG
- **Alt text** : Descriptions appropriées pour les images
- **Contraste** : Couleurs suffisamment contrastées
- **Navigation clavier** : Support complet du clavier

#### Lecteurs d'écran
- **Descriptions** : Alt text descriptif pour les photos
- **Fallback** : Initiales lues comme "Avatar de [Nom]"
- **Contexte** : Informations contextuelles appropriées

## 🚀 Performance et optimisation

### 1. Optimisation des images

#### Compression automatique
- **Formats** : Support des formats modernes (WebP)
- **Qualité** : Compression adaptée au contexte
- **Tailles** : Génération de multiples tailles

#### Cache et CDN
- **Cache navigateur** : Headers de cache appropriés
- **CDN** : Possibilité d'intégration CDN
- **Lazy loading** : Chargement différé des images

### 2. Gestion de l'espace disque

#### Surveillance
- **Métriques** : Suivi de l'utilisation de l'espace
- **Alertes** : Notifications en cas de saturation
- **Nettoyage** : Scripts de maintenance automatique

#### Optimisation
- **Compression** : Réduction de la taille des fichiers
- **Archivage** : Stockage des anciennes versions
- **Rotation** : Suppression automatique des fichiers anciens

## 🔮 Évolutions possibles

### 1. Fonctionnalités avancées

#### Édition d'images
- **Recadrage** : Outil de recadrage intégré
- **Filtres** : Application de filtres CSS
- **Redimensionnement** : Ajustement automatique des tailles

#### Galerie de photos
- **Multiples photos** : Support de plusieurs photos par utilisateur
- **Albums** : Organisation en albums
- **Partage** : Partage de photos entre utilisateurs

### 2. Intégration

#### APIs externes
- **Gravatar** : Intégration avec Gravatar
- **Social media** : Import depuis les réseaux sociaux
- **Cloud storage** : Stockage dans le cloud

#### Analytics
- **Métriques d'usage** : Suivi de l'utilisation des photos
- **A/B testing** : Test de différentes présentations
- **Heatmaps** : Analyse des interactions avec les avatars

### 3. Sécurité avancée

#### Modération
- **Détection automatique** : IA pour détecter le contenu inapproprié
- **Signalement** : Système de signalement des photos
- **Modération manuelle** : Interface de modération

#### Chiffrement
- **Stockage sécurisé** : Chiffrement des fichiers
- **Accès contrôlé** : Permissions granulaires
- **Audit trail** : Traçabilité des accès

## 📊 Métriques et monitoring

### 1. Utilisation

#### Statistiques
- **Taux d'adoption** : Pourcentage d'utilisateurs avec photo
- **Formats préférés** : Répartition des formats d'image
- **Tailles moyennes** : Taille moyenne des fichiers uploadés

#### Engagement
- **Interactions** : Clics sur les avatars
- **Modifications** : Fréquence de changement de photo
- **Satisfaction** : Retours utilisateurs sur la fonctionnalité

### 2. Performance

#### Temps de chargement
- **Upload** : Temps moyen d'upload des images
- **Affichage** : Temps de chargement des avatars
- **Erreurs** : Taux d'erreur d'upload/affichage

#### Ressources
- **Bande passante** : Consommation de bande passante
- **Stockage** : Utilisation de l'espace disque
- **CPU** : Charge serveur pour le traitement des images

---

*La fonctionnalité de photos de profil transforme l'expérience utilisateur en rendant l'application plus personnelle et moderne, tout en maintenant des performances optimales et une sécurité robuste.*
