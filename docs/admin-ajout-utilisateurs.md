# Guide d'ajout d'utilisateurs par l'administrateur

## 📋 Vue d'ensemble

La fonctionnalité d'ajout d'utilisateurs permet aux administrateurs de créer de nouveaux comptes participants directement depuis l'interface d'administration.

## 🚀 Accès à la fonctionnalité

### URL d'accès
```
http://localhost:8000/admin/participants
```

### Prérequis
- Être connecté avec un compte administrateur (ROLE_ADMIN)
- Avoir accès à l'interface d'administration

## 📝 Utilisation du formulaire

### Interface
Le formulaire d'ajout est situé en haut de la page, au-dessus de la liste des participants existants.

### Champs du formulaire

#### Informations personnelles
- **Pseudo** : Nom d'utilisateur unique (3-30 caractères)
  - Caractères autorisés : lettres, chiffres, underscore (_), tiret (-)
  - Doit être unique dans la base de données
- **Nom** : Nom de famille (2-30 caractères)
- **Prénom** : Prénom (2-30 caractères)
- **Téléphone** : Numéro de téléphone (optionnel, max 15 caractères)
- **Email** : Adresse email unique (max 180 caractères)

#### Sécurité
- **Mot de passe** : Mot de passe sécurisé (minimum 6 caractères)
- **Confirmer le mot de passe** : Répétition du mot de passe

#### Configuration
- **Site** : Site ENI d'appartenance (obligatoire)
- **Administrateur** : Cocher pour donner les droits d'admin
- **Actif** : Cocher pour activer le compte (recommandé)

### Processus de création

1. **Remplir le formulaire** : Saisir toutes les informations requises
2. **Vérifier les données** : S'assurer que les informations sont correctes
3. **Cliquer sur "Créer le participant"** : Valider la création
4. **Confirmation** : Message de succès affiché
5. **Redirection** : Retour à la liste des participants

## ✅ Validation et contraintes

### Contraintes de validation
- **Pseudo** : Unique, 3-30 caractères, format alphanumérique
- **Email** : Unique, format email valide, max 180 caractères
- **Mot de passe** : Minimum 6 caractères, confirmation requise
- **Site** : Obligatoire, doit exister dans la base de données

### Gestion des erreurs
- **Erreurs de validation** : Affichées sous chaque champ
- **Erreurs d'unicité** : Pseudo ou email déjà utilisé
- **Messages d'erreur** : Clairs et explicites

## 🔧 Fonctionnalités avancées

### Hachage automatique du mot de passe
Le mot de passe est automatiquement haché avec l'algorithme de sécurité Symfony avant stockage.

### Activation par défaut
Les nouveaux comptes sont créés actifs par défaut, permettant une connexion immédiate.

### Gestion des rôles
- **Utilisateur standard** : ROLE_USER (par défaut)
- **Administrateur** : ROLE_ADMIN (si case cochée)

## 📊 Interface utilisateur

### Design responsive
- **Desktop** : Formulaire en colonnes multiples
- **Mobile** : Formulaire en colonne unique
- **Tablette** : Adaptation automatique

### Éléments visuels
- **Icônes FontAwesome** : Pour une meilleure lisibilité
- **Couleurs Bootstrap** : Interface cohérente
- **Messages de feedback** : Succès et erreurs

### Organisation
- **Formulaire en haut** : Ajout de nouveaux utilisateurs
- **Liste en bas** : Gestion des utilisateurs existants
- **Séparation claire** : Distinction visuelle entre les sections

## 🚨 Gestion des erreurs

### Erreurs courantes
1. **Pseudo déjà utilisé** : Choisir un autre pseudo
2. **Email déjà utilisé** : Utiliser une autre adresse email
3. **Mot de passe trop court** : Minimum 6 caractères
4. **Site non sélectionné** : Choisir un site dans la liste

### Messages d'erreur
- Affichage en temps réel sous chaque champ
- Messages explicites et actionables
- Validation côté client et serveur

## 🔒 Sécurité

### Protection des données
- **Hachage des mots de passe** : Algorithme sécurisé Symfony
- **Validation stricte** : Contrôles côté serveur
- **Protection CSRF** : Token de sécurité automatique

### Permissions
- **Accès restreint** : Seuls les administrateurs peuvent ajouter des utilisateurs
- **Validation des rôles** : Contrôle d'accès strict
- **Audit trail** : Traçabilité des actions

## 📱 Compatibilité mobile

### Interface responsive
- **Formulaires adaptatifs** : Colonnes qui s'adaptent à l'écran
- **Boutons tactiles** : Taille optimisée pour le tactile
- **Navigation simplifiée** : Interface mobile-friendly

### Fonctionnalités mobiles
- **Saisie facilitée** : Champs optimisés pour mobile
- **Validation en temps réel** : Feedback immédiat
- **Messages d'erreur clairs** : Lisibles sur petit écran

## 🎯 Bonnes pratiques

### Pour les administrateurs
- **Vérifier l'unicité** : S'assurer que pseudo et email sont uniques
- **Mot de passe sécurisé** : Recommander des mots de passe forts
- **Activation immédiate** : Laisser la case "Actif" cochée
- **Site correct** : Vérifier l'attribution au bon site ENI

### Gestion des comptes
- **Documentation** : Noter les informations importantes
- **Communication** : Informer l'utilisateur de ses identifiants
- **Suivi** : Vérifier que l'utilisateur peut se connecter

## 🔄 Intégration avec l'existant

### Cohérence avec l'inscription
- **Même validation** : Contraintes identiques à l'inscription publique
- **Même structure** : Données stockées de la même manière
- **Compatibilité** : Fonctionne avec tous les systèmes existants

### Gestion des rôles
- **Intégration sécurité** : Compatible avec le système d'authentification
- **Permissions** : Respect des règles d'autorisation
- **Workflow** : Intégration avec les fonctionnalités existantes

## 📞 Support et dépannage

### Problèmes courants
- **Erreur de validation** : Vérifier tous les champs obligatoires
- **Pseudo/email existant** : Utiliser des identifiants uniques
- **Problème de site** : S'assurer que le site existe

### Solutions
- **Réinitialiser le formulaire** : Bouton "Réinitialiser"
- **Vérifier les données** : Relire attentivement les informations
- **Contacter le support** : En cas de problème persistant

---

*Cette fonctionnalité améliore significativement la gestion des utilisateurs en permettant aux administrateurs de créer des comptes rapidement et efficacement.*
