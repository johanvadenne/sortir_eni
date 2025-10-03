# Formulaires Symfony - Documentation

## Vue d'ensemble

Cette documentation décrit tous les formulaires (FormType) implémentés dans l'application Sortir, avec leurs champs, validations et utilisations.

## Formulaires Implémentés

### 1. SortieType

**Utilisation** : Création et modification des sorties

**Champs** :
- `nom` (TextType) : Nom de la sortie
- `dateHeureDebut` (DateTimeType) : Date et heure de début
- `dateLimiteInscription` (DateTimeType) : Date limite d'inscription
- `nbInscriptionsMax` (IntegerType) : Nombre maximum d'inscriptions
- `duree` (IntegerType) : Durée en minutes (optionnel)
- `infosSortie` (TextareaType) : Description et informations (optionnel)
- `lieu` (EntityType) : Lieu de la sortie
- `urlPhoto` (UrlType) : URL de la photo (optionnel)

**Validations** :
- Nom obligatoire
- Date de début dans le futur
- Date limite antérieure à la date de début
- Nombre d'inscriptions ≥ 1
- Lieu obligatoire

**Exemple d'utilisation** :
```php
$form = $this->createForm(SortieType::class, $sortie);
```

### 2. LieuType

**Utilisation** : Création et modification des lieux

**Champs** :
- `nom` (TextType) : Nom du lieu
- `rue` (TextType) : Adresse (optionnel)
- `latitude` (NumberType) : Latitude GPS (optionnel)
- `longitude` (NumberType) : Longitude GPS (optionnel)
- `ville` (EntityType) : Ville du lieu

**Validations** :
- Nom obligatoire
- Latitude entre -90 et 90
- Longitude entre -180 et 180
- Ville obligatoire

**Exemple d'utilisation** :
```php
$form = $this->createForm(LieuType::class, $lieu);
```

### 3. VilleType

**Utilisation** : Création et modification des villes

**Champs** :
- `nom` (TextType) : Nom de la ville
- `codePostal` (TextType) : Code postal

**Validations** :
- Nom obligatoire (2-100 caractères)
- Code postal obligatoire (5 chiffres exactement)

**Exemple d'utilisation** :
```php
$form = $this->createForm(VilleType::class, $ville);
```

### 4. SiteType

**Utilisation** : Création et modification des sites

**Champs** :
- `nom` (TextType) : Nom du site

**Validations** :
- Nom obligatoire (2-100 caractères)

**Exemple d'utilisation** :
```php
$form = $this->createForm(SiteType::class, $site);
```

### 5. ProfilType

**Utilisation** : Modification du profil utilisateur

**Champs** :
- `pseudo` (TextType) : Pseudo de l'utilisateur
- `nom` (TextType) : Nom de famille
- `prenom` (TextType) : Prénom
- `telephone` (TelType) : Numéro de téléphone (optionnel)
- `email` (EmailType) : Adresse email
- `site` (EntityType) : Site de rattachement

**Validations** :
- Pseudo obligatoire (3-50 caractères)
- Nom obligatoire (2-50 caractères)
- Prénom obligatoire (2-50 caractères)
- Téléphone au format français (optionnel)
- Email valide et obligatoire
- Site obligatoire

**Exemple d'utilisation** :
```php
$form = $this->createForm(ProfilType::class, $participant);
```

### 6. AnnulationType

**Utilisation** : Annulation d'une sortie avec motif

**Champs** :
- `motif` (TextareaType) : Motif d'annulation (optionnel)

**Validations** :
- Motif limité à 500 caractères (optionnel)

**Exemple d'utilisation** :
```php
$form = $this->createForm(AnnulationType::class);
```

### 7. ChangePasswordType

**Utilisation** : Changement de mot de passe

**Champs** :
- `oldPassword` (PasswordType) : Ancien mot de passe
- `newPassword` (RepeatedType) : Nouveau mot de passe (confirmation)

**Validations** :
- Ancien mot de passe obligatoire
- Nouveau mot de passe obligatoire (minimum 6 caractères)
- Confirmation du nouveau mot de passe

**Exemple d'utilisation** :
```php
$form = $this->createForm(ChangePasswordType::class);
```

## Types de Champs Utilisés

### TextType
- **Utilisation** : Champs texte simples
- **Options** : `required`, `label`, `attr`, `constraints`

### TextareaType
- **Utilisation** : Zones de texte multilignes
- **Options** : `rows`, `attr`

### EmailType
- **Utilisation** : Champs email avec validation automatique
- **Options** : Validation email intégrée

### TelType
- **Utilisation** : Champs téléphone
- **Options** : Validation téléphone

### IntegerType
- **Utilisation** : Champs numériques entiers
- **Options** : `attr` avec `min`, `max`

### NumberType
- **Utilisation** : Champs numériques décimaux
- **Options** : `scale` pour la précision

### DateTimeType
- **Utilisation** : Champs date et heure
- **Options** : `widget` => `single_text`, `html5` => `true`

### UrlType
- **Utilisation** : Champs URL avec validation
- **Options** : Validation URL intégrée

### EntityType
- **Utilisation** : Sélection d'entités
- **Options** : `class`, `choice_label`

### PasswordType
- **Utilisation** : Champs mot de passe
- **Options** : Masquage automatique

### RepeatedType
- **Utilisation** : Confirmation de mot de passe
- **Options** : `type`, `first_options`, `second_options`

## Contraintes de Validation

### NotBlank
- **Utilisation** : Champs obligatoires
- **Message** : Personnalisable

### Length
- **Utilisation** : Longueur de chaîne
- **Options** : `min`, `max`, `minMessage`, `maxMessage`

### Email
- **Utilisation** : Validation email
- **Message** : Personnalisable

### Regex
- **Utilisation** : Validation par expression régulière
- **Options** : `pattern`, `message`

### GreaterThan
- **Utilisation** : Valeur supérieure
- **Options** : `value`, `message`

### LessThan
- **Utilisation** : Valeur inférieure
- **Options** : `propertyPath`, `message`

### GreaterThanOrEqual
- **Utilisation** : Valeur supérieure ou égale
- **Options** : `value`, `message`

### Range
- **Utilisation** : Valeur dans une plage
- **Options** : `min`, `max`, `notInRangeMessage`

## Bonnes Pratiques

### 1. Messages d'Erreur
- Toujours personnaliser les messages d'erreur
- Messages en français
- Messages explicites et utiles

### 2. Placeholders
- Ajouter des placeholders pour guider l'utilisateur
- Exemples de format attendu

### 3. Validation
- Validation côté client et serveur
- Contraintes appropriées pour chaque champ
- Messages d'erreur clairs

### 4. Accessibilité
- Labels appropriés
- Attributs ARIA si nécessaire
- Structure sémantique

### 5. Responsive
- Formulaires adaptatifs
- Grilles Bootstrap
- Champs empilables sur mobile

## Exemples d'Intégration

### Dans un Contrôleur
```php
public function create(Request $request): Response
{
    $entity = new Entity();
    $form = $this->createForm(EntityType::class, $entity);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        // Traitement
        $this->entityManager->persist($entity);
        $this->entityManager->flush();

        $this->addFlash('success', 'Entité créée avec succès.');
        return $this->redirectToRoute('entity_index');
    }

    return $this->render('entity/create.html.twig', [
        'form' => $form->createView()
    ]);
}
```

### Dans un Template
```twig
{{ form_start(form) }}
    <div class="row">
        <div class="col-md-6">
            {{ form_row(form.nom) }}
        </div>
        <div class="col-md-6">
            {{ form_row(form.email) }}
        </div>
    </div>

    <div class="mt-3">
        <button type="submit" class="btn btn-primary">Enregistrer</button>
        <a href="{{ path('entity_index') }}" class="btn btn-secondary">Annuler</a>
    </div>
{{ form_end(form) }}
```

## Tests des Formulaires

### Test de Validation
```php
public function testSortieValidation(): void
{
    $sortie = new Sortie();
    $sortie->setNom(''); // Nom vide

    $errors = $this->validator->validate($sortie);
    $this->assertCount(1, $errors);
    $this->assertEquals('Le nom de la sortie est obligatoire', $errors[0]->getMessage());
}
```

### Test de Soumission
```php
public function testFormSubmission(): void
{
    $form = $this->createForm(SortieType::class);
    $form->submit([
        'nom' => 'Test Sortie',
        'dateHeureDebut' => '2024-12-01T10:00:00',
        // ... autres champs
    ]);

    $this->assertTrue($form->isValid());
}
```
