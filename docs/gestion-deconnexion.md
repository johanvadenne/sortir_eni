# Gestion de la déconnexion - ENI-Sortir

## 📋 Vue d'ensemble

La déconnexion dans l'application ENI-Sortir est gérée par le système de sécurité de Symfony, avec une interface utilisateur intuitive et des mécanismes de sécurité robustes.

## 🔧 Configuration technique

### 1. Configuration de sécurité

#### Fichier `config/packages/security.yaml`
```yaml
security:
    firewalls:
        main:
            logout:
                path: logout
                target: login
```

**Paramètres de déconnexion :**
- **`path: logout`** : URL de déconnexion (`/logout`)
- **`target: login`** : Redirection vers la page de connexion après déconnexion

#### Contrôle d'accès
```yaml
access_control:
    - { path: ^/logout, roles: PUBLIC_ACCESS }
```

La route de déconnexion est accessible publiquement (pas besoin d'être connecté pour se déconnecter).

### 2. Routes de déconnexion

#### Route automatique Symfony
```yaml
# config/routes/security.yaml
_security_logout:
    resource: security.route_loader.logout
    type: service
```

Cette route est automatiquement générée par Symfony et pointe vers `/logout`.

#### Contrôleur de déconnexion
```php
// src/Controller/SecurityController.php
#[Route('/logout', name: 'logout')]
public function logout(): void
{
    // Cette méthode peut rester vide - elle sera interceptée par la clé logout de votre firewall
    throw new \LogicException('Cette méthode peut rester vide - elle sera interceptée par la clé logout de votre firewall.');
}
```

**Note importante :** Cette méthode ne sera jamais exécutée car Symfony intercepte la requête avant qu'elle n'atteigne le contrôleur.

## 🎨 Interface utilisateur

### 1. Menu de navigation

#### Affichage conditionnel
```twig
<!-- templates/base.html.twig -->
{% if app.user %}
    <li class="nav-item dropdown">
        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
            <i class="bi bi-person-circle"></i>
            {{ app.user.prenom }} {{ app.user.nom }}
            {% if app.user.administrateur %}
                <span class="badge bg-danger ms-1">Admin</span>
            {% endif %}
        </a>
        <ul class="dropdown-menu">
            <!-- Menu utilisateur -->
            <li><a class="dropdown-item" href="{{ path('profil_show') }}">
                <i class="bi bi-person"></i> Mon profil
            </a></li>

            <!-- Menu admin (si applicable) -->
            {% if is_granted('ROLE_ADMIN') %}
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item" href="{{ path('admin_dashboard') }}">
                    <i class="bi bi-gear"></i> Administration
                </a></li>
            {% endif %}

            <!-- Lien de déconnexion -->
            <li><hr class="dropdown-divider"></li>
            <li><a class="dropdown-item" href="{{ path('logout') }}">
                <i class="bi bi-box-arrow-right"></i> Déconnexion
            </a></li>
        </ul>
    </li>
{% else %}
    <!-- Lien de connexion si non connecté -->
    <li class="nav-item">
        <a class="nav-link" href="{{ path('login') }}">
            <i class="bi bi-box-arrow-in-right"></i> Connexion
        </a>
    </li>
{% endif %}
```

### 2. Éléments visuels

#### Icônes et design
- **Icône de déconnexion** : `bi-box-arrow-right` (Bootstrap Icons)
- **Séparateur** : Ligne horizontale avant le lien de déconnexion
- **Style** : Dropdown menu avec Bootstrap
- **Responsive** : Adaptation mobile avec navbar toggler

#### Informations utilisateur
- **Nom affiché** : Prénom + Nom de l'utilisateur
- **Badge admin** : Indication visuelle pour les administrateurs
- **Avatar** : Icône de personne

## 🔄 Processus de déconnexion

### 1. Déclenchement
1. **Clic sur le lien** : Utilisateur clique sur "Déconnexion"
2. **Requête GET** : Navigation vers `/logout`
3. **Interception Symfony** : Le firewall intercepte la requête

### 2. Traitement
1. **Invalidation de session** : Symfony invalide la session utilisateur
2. **Nettoyage des cookies** : Suppression des cookies de session
3. **Redirection** : Redirection vers la page de connexion

### 3. Résultat
1. **Page de connexion** : Utilisateur redirigé vers `/login`
2. **Session fermée** : Plus d'accès aux pages protégées
3. **Interface mise à jour** : Menu affiche le lien "Connexion"

## 🔒 Sécurité

### 1. Mécanismes de sécurité

#### Invalidation de session
- **Session PHP** : Destruction complète de la session
- **Cookies** : Suppression des cookies de session
- **Token CSRF** : Invalidation des tokens de sécurité

#### Protection contre les attaques
- **CSRF** : Protection contre les attaques Cross-Site Request Forgery
- **Session hijacking** : Prévention du vol de session
- **Logout forcé** : Possibilité de déconnexion à distance

### 2. Gestion des erreurs

#### Cas d'erreur
- **Session expirée** : Redirection automatique vers la connexion
- **Utilisateur inactif** : Déconnexion automatique après inactivité
- **Erreur serveur** : Gestion gracieuse des erreurs

#### Messages utilisateur
- **Déconnexion réussie** : Pas de message spécifique (redirection immédiate)
- **Erreur de déconnexion** : Gestion par le système d'erreurs Symfony

## 📱 Responsive et accessibilité

### 1. Interface mobile
- **Menu hamburger** : Navigation mobile avec Bootstrap
- **Dropdown tactile** : Support des gestes tactiles
- **Boutons adaptés** : Taille optimisée pour le tactile

### 2. Accessibilité
- **Navigation clavier** : Support de la navigation au clavier
- **Lecteurs d'écran** : Compatible avec les technologies d'assistance
- **Contraste** : Respect des standards d'accessibilité

## 🎯 Expérience utilisateur

### 1. Facilité d'utilisation
- **Un clic** : Déconnexion en un seul clic
- **Menu intuitif** : Lien de déconnexion clairement identifié
- **Feedback visuel** : Icône et texte explicites

### 2. Sécurité perçue
- **Confirmation implicite** : Pas de popup de confirmation (UX fluide)
- **Redirection claire** : Retour à la page de connexion
- **État cohérent** : Interface mise à jour immédiatement

## 🔧 Personnalisation

### 1. Modification de la redirection
```yaml
# config/packages/security.yaml
security:
    firewalls:
        main:
            logout:
                path: logout
                target: home  # Rediriger vers l'accueil au lieu de login
```

### 2. Ajout de messages
```php
// Dans un contrôleur personnalisé
public function customLogout(): Response
{
    $this->addFlash('success', 'Vous avez été déconnecté avec succès.');
    return $this->redirectToRoute('login');
}
```

### 3. Logs de déconnexion
```php
// Dans un EventListener
public function onLogoutSuccess(LogoutEvent $event): void
{
    $user = $event->getToken()->getUser();
    $this->logger->info('User logged out', ['user' => $user->getUserIdentifier()]);
}
```

## 📊 Monitoring et logs

### 1. Logs de sécurité
- **Déconnexions** : Enregistrement des déconnexions
- **Tentatives d'accès** : Logs des tentatives après déconnexion
- **Sessions expirées** : Suivi des sessions expirées

### 2. Métriques
- **Durée de session** : Temps moyen de connexion
- **Fréquence de déconnexion** : Nombre de déconnexions par utilisateur
- **Pages visitées** : Suivi de l'activité avant déconnexion

## 🚀 Bonnes pratiques

### 1. Sécurité
- **Déconnexion automatique** : Après une période d'inactivité
- **Déconnexion forcée** : Possibilité pour les admins
- **Nettoyage des données** : Suppression des données sensibles

### 2. UX
- **Feedback immédiat** : Redirection rapide après déconnexion
- **État cohérent** : Interface mise à jour immédiatement
- **Navigation intuitive** : Lien de déconnexion facilement accessible

### 3. Performance
- **Session légère** : Nettoyage complet des données de session
- **Cache invalidation** : Invalidation du cache utilisateur
- **Redirection optimisée** : Redirection directe sans traitement supplémentaire

## 🔮 Évolutions possibles

### 1. Améliorations UX
- **Confirmation de déconnexion** : Popup de confirmation optionnel
- **Déconnexion automatique** : Avertissement avant expiration de session
- **Historique de connexion** : Affichage des dernières connexions

### 2. Sécurité avancée
- **Déconnexion à distance** : Déconnexion depuis d'autres sessions
- **Audit trail** : Traçabilité complète des connexions/déconnexions
- **Multi-factor logout** : Déconnexion avec confirmation supplémentaire

### 3. Intégration
- **SSO logout** : Déconnexion des systèmes externes
- **Webhooks** : Notifications de déconnexion
- **API logout** : Déconnexion via API REST

---

*La gestion de la déconnexion dans ENI-Sortir est robuste, sécurisée et offre une excellente expérience utilisateur grâce à l'intégration native de Symfony Security.*
