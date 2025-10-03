# Affichage des photos de sorties - ENI-Sortir

## 📋 Vue d'ensemble

Cette documentation décrit l'implémentation de l'affichage des photos de sorties comme fond de carte sur la page d'accueil et dans les pages de détail. Les photos sont maintenant visibles et améliorent considérablement l'expérience utilisateur.

## 🎨 Fonctionnalités implémentées

### 1. Affichage sur la page d'accueil

#### Cartes avec photos
- **Fond d'image** : Les photos sont affichées comme arrière-plan des cartes de sorties
- **Overlay dégradé** : Superposition avec dégradé pour améliorer la lisibilité du texte
- **Texte blanc** : Titre et badge d'état en blanc avec ombre portée
- **Hauteur fixe** : 200px sur desktop, 150px sur mobile

#### Cartes sans photos
- **Design par défaut** : Affichage classique avec header coloré
- **Cohérence visuelle** : Même structure que les cartes avec photos

### 2. Affichage sur la page de détail

#### Image en en-tête
- **Hauteur** : 300px pour une meilleure visibilité
- **Titre superposé** : Nom de la sortie affiché sur l'image
- **Badge d'état** : Positionné en haut à droite
- **Overlay subtil** : Dégradé léger pour la lisibilité

#### Fallback sans photo
- **Titre classique** : Affichage du titre en header normal si pas de photo
- **Cohérence** : Même information affichée différemment

## 🔧 Implémentation technique

### 1. Structure HTML

#### Page d'accueil
```twig
{% if sortie.urlPhoto %}
    <div class="card-image-container"
         style="height: 200px; background-size: cover; background-position: center; background-repeat: no-repeat; position: relative;"
         data-bg-image="{{ sortie.urlPhoto }}">
        <div class="card-image-overlay" style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; background: linear-gradient(to bottom, rgba(0,0,0,0.3) 0%, rgba(0,0,0,0.7) 100%);"></div>
        <div class="card-header d-flex justify-content-between align-items-center" style="position: relative; z-index: 2; background: transparent; border: none;">
            <h5 class="card-title mb-0 text-white" style="text-shadow: 2px 2px 4px rgba(0,0,0,0.8);">{{ sortie.nom }}</h5>
            <span class="badge bg-success" style="text-shadow: 1px 1px 2px rgba(0,0,0,0.8);">
                {{ sortie.etat.libelle }}
            </span>
        </div>
    </div>
{% else %}
    <!-- Affichage classique sans photo -->
{% endif %}
```

#### Page de détail
```twig
{% if sortie.urlPhoto %}
    <div class="card shadow mb-4">
        <div class="card-image-detail"
             style="height: 300px; background-image: url('{{ sortie.urlPhoto }}'); background-size: cover; background-position: center; background-repeat: no-repeat; position: relative; border-radius: 0.375rem 0.375rem 0 0;">
            <div class="card-image-overlay" style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; background: linear-gradient(to bottom, rgba(0,0,0,0.2) 0%, rgba(0,0,0,0.5) 100%); border-radius: 0.375rem 0.375rem 0 0;"></div>
            <div class="position-absolute top-0 start-0 p-3">
                <h2 class="text-white mb-0" style="text-shadow: 2px 2px 4px rgba(0,0,0,0.8);">{{ sortie.nom }}</h2>
            </div>
            <div class="position-absolute top-0 end-0 p-3">
                <span class="badge bg-success fs-6" style="text-shadow: 1px 1px 2px rgba(0,0,0,0.8);">
                    {{ sortie.etat.libelle }}
                </span>
            </div>
        </div>
    </div>
{% endif %}
```

### 2. Styles CSS

#### Animations et transitions
```css
.sortie-card {
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    overflow: hidden;
}

.sortie-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.15) !important;
}

.card-image-overlay {
    transition: opacity 0.3s ease;
}

.sortie-card:hover .card-image-overlay {
    opacity: 0.8;
}
```

#### Responsive design
```css
@media (max-width: 768px) {
    .card-image-container {
        height: 150px !important;
    }

    .card-title {
        font-size: 1rem;
    }
}
```

#### Effet de chargement
```css
.card-image-container {
    background-color: #f8f9fa;
    background-image:
        linear-gradient(45deg, transparent 25%, rgba(255,255,255,.2) 25%, rgba(255,255,255,.2) 50%, transparent 50%, transparent 75%, rgba(255,255,255,.2) 75%, rgba(255,255,255,.2)),
        linear-gradient(-45deg, transparent 25%, rgba(255,255,255,.2) 25%, rgba(255,255,255,.2) 50%, transparent 50%, transparent 75%, rgba(255,255,255,.2) 75%, rgba(255,255,255,.2));
    background-size: 20px 20px;
    background-position: 0 0, 0 10px;
}
```

### 3. JavaScript

#### Gestion du chargement des images
```javascript
document.addEventListener('DOMContentLoaded', function() {
    const imageContainers = document.querySelectorAll('.card-image-container[data-bg-image]');

    imageContainers.forEach(function(container) {
        const imageUrl = container.getAttribute('data-bg-image');
        const testImage = new Image();

        testImage.onload = function() {
            container.style.backgroundImage = `url('${imageUrl}')`;
            container.classList.add('image-loaded');
        };

        testImage.onerror = function() {
            // Image de fallback SVG encodée en base64
            container.style.backgroundImage = `url('data:image/svg+xml;base64,...')`;
            container.classList.add('image-error');
        };

        testImage.src = imageUrl;
    });
});
```

#### Animation d'apparition
```javascript
const cards = document.querySelectorAll('.sortie-card');
cards.forEach(function(card, index) {
    card.style.opacity = '0';
    card.style.transform = 'translateY(20px)';

    setTimeout(function() {
        card.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
        card.style.opacity = '1';
        card.style.transform = 'translateY(0)';
    }, index * 100);
});
```

## 🔒 Validation et sécurité

### 1. Validation des URLs

#### Contraintes du formulaire
```php
->add('urlPhoto', UrlType::class, [
    'label' => 'URL de la photo',
    'required' => false,
    'attr' => [
        'class' => 'form-control',
        'placeholder' => 'https://example.com/photo.jpg',
        'pattern' => '^https?://.+\.(jpg|jpeg|png|gif|webp)(\?.*)?$',
        'title' => 'URL doit pointer vers une image (jpg, jpeg, png, gif, webp)'
    ],
    'help' => 'Lien vers une image représentative de votre sortie (optionnel). Formats acceptés: JPG, PNG, GIF, WebP',
    'constraints' => [
        new \Symfony\Component\Validator\Constraints\Regex([
            'pattern' => '/^https?:\/\/.+\.(jpg|jpeg|png|gif|webp)(\?.*)?$/i',
            'message' => 'L\'URL doit pointer vers une image valide (JPG, PNG, GIF, WebP)',
            'match' => true
        ])
    ]
])
```

#### Formats acceptés
- **JPG/JPEG** : Images photographiques
- **PNG** : Images avec transparence
- **GIF** : Images animées ou statiques
- **WebP** : Format moderne optimisé

### 2. Gestion des erreurs

#### Images de fallback
- **SVG encodé** : Image de remplacement en base64
- **Message explicite** : "Image non disponible"
- **Design cohérent** : Même style que les autres cartes

#### Gestion des erreurs de chargement
- **Test préalable** : Vérification de la disponibilité de l'image
- **Fallback automatique** : Remplacement en cas d'erreur
- **Feedback visuel** : Classes CSS pour identifier les erreurs

## 📱 Responsive et accessibilité

### 1. Design responsive

#### Breakpoints
- **Desktop** : 200px de hauteur pour les cartes
- **Tablet** : Adaptation automatique
- **Mobile** : 150px de hauteur pour optimiser l'espace

#### Optimisations mobiles
- **Taille de police** : Réduction sur petits écrans
- **Espacement** : Ajustement des marges et paddings
- **Lisibilité** : Contraste optimisé pour tous les écrans

### 2. Accessibilité

#### Contraste et lisibilité
- **Ombre portée** : Amélioration de la lisibilité du texte blanc
- **Dégradé d'overlay** : Assure un contraste suffisant
- **Tailles de police** : Respect des standards d'accessibilité

#### Navigation clavier
- **Focus visible** : Indicateurs de focus sur les éléments interactifs
- **Ordre logique** : Navigation cohérente au clavier
- **Descriptions** : Alt text et descriptions appropriées

## 🎯 Expérience utilisateur

### 1. Avantages

#### Visuel attractif
- **Impact visuel** : Les photos rendent les sorties plus attractives
- **Reconnaissance rapide** : Identification visuelle des sorties
- **Professionnalisme** : Interface moderne et soignée

#### Performance
- **Chargement optimisé** : Test préalable des images
- **Fallback gracieux** : Gestion des erreurs sans impact UX
- **Animations fluides** : Transitions et effets visuels

### 2. Fonctionnalités

#### Interactions
- **Hover effects** : Animations au survol des cartes
- **Chargement progressif** : Affichage des cartes avec délai
- **Feedback visuel** : Indicateurs de chargement et d'erreur

#### Personnalisation
- **URLs flexibles** : Support de différents hébergeurs d'images
- **Formats multiples** : Compatibilité avec tous les formats web
- **Tailles adaptatives** : Images redimensionnées automatiquement

## 🔮 Évolutions possibles

### 1. Améliorations techniques

#### Optimisation des images
- **Lazy loading** : Chargement différé des images
- **Compression** : Optimisation automatique des images
- **CDN** : Utilisation d'un CDN pour les images

#### Cache et performance
- **Cache navigateur** : Mise en cache des images
- **Preload** : Préchargement des images importantes
- **Service Worker** : Gestion offline des images

### 2. Fonctionnalités avancées

#### Upload d'images
- **Upload local** : Possibilité d'uploader des images
- **Redimensionnement** : Ajustement automatique des tailles
- **Galerie** : Sélection parmi plusieurs images

#### Filtres et effets
- **Filtres CSS** : Application de filtres sur les images
- **Overlay personnalisé** : Choix des couleurs d'overlay
- **Animations avancées** : Effets de transition plus sophistiqués

### 3. Intégration

#### APIs externes
- **Unsplash** : Intégration avec une banque d'images
- **Google Images** : Recherche d'images automatique
- **Social media** : Import depuis les réseaux sociaux

#### Analytics
- **Métriques d'engagement** : Suivi des interactions avec les images
- **A/B testing** : Test de différentes présentations
- **Heatmaps** : Analyse des zones d'intérêt

## 📊 Métriques et monitoring

### 1. Performance

#### Temps de chargement
- **Images** : Temps de chargement des images de fond
- **Fallbacks** : Fréquence d'utilisation des images de remplacement
- **Erreurs** : Taux d'erreur de chargement des images

#### Optimisation
- **Cache hit ratio** : Efficacité du cache
- **Bandwidth** : Consommation de bande passante
- **Mobile performance** : Performance sur appareils mobiles

### 2. Utilisation

#### Engagement
- **Clics sur les cartes** : Taux de clic sur les sorties avec photos
- **Temps de visualisation** : Durée de consultation des sorties
- **Conversion** : Taux d'inscription aux sorties avec photos

#### Feedback utilisateur
- **Satisfaction** : Retours sur l'interface visuelle
- **Problèmes** : Signalement d'images non disponibles
- **Suggestions** : Améliorations proposées par les utilisateurs

---

*L'affichage des photos de sorties transforme l'expérience utilisateur en rendant l'interface plus attractive et professionnelle, tout en maintenant des performances optimales et une accessibilité complète.*
