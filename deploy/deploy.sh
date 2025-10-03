#!/bin/bash

# Script de déploiement pour Symfony + MySQL
# Usage: ./deploy.sh

echo "🚀 Début du déploiement de l'application Sortir..."

# 1. Vérifier les prérequis
echo "📋 Vérification des prérequis..."

# Vérifier PHP
if ! command -v php &> /dev/null; then
    echo "❌ PHP n'est pas installé"
    exit 1
fi

# Vérifier Composer
if ! command -v composer &> /dev/null; then
    echo "❌ Composer n'est pas installé"
    exit 1
fi

# Vérifier MySQL
if ! command -v mysql &> /dev/null; then
    echo "❌ MySQL n'est pas installé"
    exit 1
fi

echo "✅ Tous les prérequis sont installés"

# 2. Installer les dépendances
echo "📦 Installation des dépendances..."
composer install --no-dev --optimize-autoloader

# 3. Configurer l'environnement de production
echo "⚙️ Configuration de l'environnement de production..."
if [ ! -f .env.local ]; then
    echo "⚠️ Fichier .env.local manquant. Copiez deploy/env_prod_template.txt vers .env.local"
    echo "   et modifiez les valeurs selon votre configuration."
    exit 1
fi

# 4. Vider le cache
echo "🧹 Nettoyage du cache..."
php bin/console cache:clear --env=prod

# 5. Créer le schéma de base de données
echo "🗄️ Création du schéma de base de données..."
php bin/console doctrine:database:create --env=prod --if-not-exists
php bin/console doctrine:migrations:migrate --env=prod --no-interaction

# 6. Charger les fixtures de base
echo "📊 Chargement des données de base..."
php bin/console doctrine:fixtures:load --env=prod --group=etat --no-interaction

# 7. Optimiser l'autoloader
echo "⚡ Optimisation de l'autoloader..."
composer dump-autoload --optimize --classmap-authoritative

# 8. Vérifier les permissions
echo "🔐 Configuration des permissions..."
chmod -R 755 var/
chmod -R 755 public/

echo "✅ Déploiement terminé avec succès!"
echo "🌐 Votre application est prête à être utilisée"

