@echo off
REM Script de déploiement pour Windows
REM Usage: deploy.bat

echo 🚀 Début du déploiement de l'application Sortir...

REM 1. Vérifier les prérequis
echo 📋 Vérification des prérequis...

REM Vérifier PHP
php --version >nul 2>&1
if %errorlevel% neq 0 (
    echo ❌ PHP n'est pas installé
    pause
    exit /b 1
)

REM Vérifier Composer
composer --version >nul 2>&1
if %errorlevel% neq 0 (
    echo ❌ Composer n'est pas installé
    pause
    exit /b 1
)

echo ✅ Tous les prérequis sont installés

REM 2. Installer les dépendances
echo 📦 Installation des dépendances...
composer install --no-dev --optimize-autoloader

REM 3. Configurer l'environnement de production
echo ⚙️ Configuration de l'environnement de production...
if not exist .env.local (
    echo ⚠️ Fichier .env.local manquant. Copiez deploy\env_prod_template.txt vers .env.local
    echo    et modifiez les valeurs selon votre configuration.
    pause
    exit /b 1
)

REM 4. Vider le cache
echo 🧹 Nettoyage du cache...
php bin/console cache:clear --env=prod

REM 5. Créer le schéma de base de données
echo 🗄️ Création du schéma de base de données...
php bin/console doctrine:database:create --env=prod --if-not-exists
php bin/console doctrine:migrations:migrate --env=prod --no-interaction

REM 6. Charger les fixtures de base
echo 📊 Chargement des données de base...
php bin/console doctrine:fixtures:load --env=prod --group=etat --no-interaction

REM 7. Optimiser l'autoloader
echo ⚡ Optimisation de l'autoloader...
composer dump-autoload --optimize --classmap-authoritative

echo ✅ Déploiement terminé avec succès!
echo 🌐 Votre application est prête à être utilisée
pause

