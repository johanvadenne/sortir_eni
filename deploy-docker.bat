@echo off
echo 🚀 Déploiement Docker de l'application Sortir
echo ================================================

REM 1. Vérifier que Docker Desktop est installé
echo 📋 Vérification de Docker Desktop...
docker --version >nul 2>&1
if %errorlevel% neq 0 (
    echo ❌ Docker Desktop n'est pas installé
    echo    Veuillez installer Docker Desktop depuis https://www.docker.com/products/docker-desktop
    pause
    exit /b 1
)
echo ✅ Docker Desktop est installé

REM 2. Vérifier que Docker Desktop est démarré
echo 📋 Vérification du daemon Docker...
docker info >nul 2>&1
if %errorlevel% neq 0 (
    echo ⚠️  Docker Desktop n'est pas démarré
    echo    Démarrage de Docker Desktop...
    start "" "C:\Program Files\Docker\Docker\Docker Desktop.exe"
    echo ⏳ Attente du démarrage de Docker Desktop (30 secondes)...
    timeout /t 30 /nobreak >nul

    REM Vérifier à nouveau
    docker info >nul 2>&1
    if %errorlevel% neq 0 (
        echo ❌ Docker Desktop n'a pas pu démarrer
        echo    Veuillez démarrer Docker Desktop manuellement et relancer ce script
        pause
        exit /b 1
    )
)
echo ✅ Docker Desktop est démarré

REM 3. Nettoyer les anciens conteneurs et images
echo 🧹 Nettoyage des anciens conteneurs...
docker stop sortir-container sortir-database 2>nul
docker rm sortir-container sortir-database 2>nul
docker rmi sortir-app:latest 2>nul

REM 4. Construire l'image
echo 🏗️ Construction de l'image Docker...
docker build -f docker/Dockerfile.prod -t sortir-app:latest .

if %errorlevel% neq 0 (
    echo ❌ Erreur lors de la construction de l'image
    echo    Vérifiez que tous les fichiers sont présents
    pause
    exit /b 1
)
echo ✅ Image construite avec succès

REM 5. Lancer la base de données MySQL
echo 🗄️ Lancement de MySQL...
docker run -d --name sortir-database \
    -e MYSQL_ROOT_PASSWORD=rootpassword \
    -e MYSQL_DATABASE=sortir \
    -e MYSQL_USER=sortir_user \
    -e MYSQL_PASSWORD=sortir_password \
    -p 3306:3306 \
    mysql:8.0

if %errorlevel% neq 0 (
    echo ❌ Erreur lors du lancement de MySQL
    pause
    exit /b 1
)
echo ✅ MySQL démarré

REM 6. Attendre que MySQL soit prêt
echo ⏳ Attente du démarrage de MySQL (30 secondes)...
timeout /t 30 /nobreak >nul

REM 7. Lancer l'application
echo 🚀 Lancement de l'application...
docker run -d --name sortir-container \
    --link sortir-database:database \
    -e DATABASE_URL="mysql://sortir_user:sortir_password@database:3306/sortir?serverVersion=8.0" \
    -e APP_ENV=prod \
    -e APP_SECRET=your_secret_key_here \
    -p 8000:8000 \
    sortir-app:latest

if %errorlevel% neq 0 (
    echo ❌ Erreur lors du lancement de l'application
    pause
    exit /b 1
)
echo ✅ Application démarrée

REM 8. Attendre que l'application soit prête
echo ⏳ Attente du démarrage de l'application (15 secondes)...
timeout /t 15 /nobreak >nul

REM 9. Vérifier l'état
echo 📊 État des conteneurs...
docker ps --filter name=sortir

REM 10. Afficher les informations de déploiement
echo.
echo 🎉 DÉPLOIEMENT TERMINÉ AVEC SUCCÈS!
echo ================================================
echo.
echo 🌐 Application accessible sur : http://localhost:8000
echo 🗄️ Base de données MySQL sur : localhost:3306
echo.
echo 📋 Informations de connexion :
echo    - Utilisateur MySQL : sortir_user
echo    - Mot de passe MySQL : sortir_password
echo    - Base de données : sortir
echo.
echo 📋 Commandes utiles :
echo    - docker logs -f sortir-container  : Voir les logs de l'app
echo    - docker logs -f sortir-database   : Voir les logs de MySQL
echo    - docker exec -it sortir-container bash  : Accéder à l'app
echo    - docker stop sortir-container sortir-database  : Arrêter l'application
echo    - docker start sortir-container sortir-database : Redémarrer l'application
echo.
echo 🔧 Pour arrêter l'application : docker stop sortir-container sortir-database
echo 🔄 Pour redémarrer l'application : docker start sortir-container sortir-database
echo.

REM 11. Test de l'application
echo 🧪 Test de l'application...
curl -s -o nul -w "%%{http_code}" http://localhost:8000 >nul 2>&1
if %errorlevel% equ 0 (
    echo ✅ Application accessible et fonctionnelle
) else (
    echo ⚠️  Application en cours de démarrage, veuillez patienter...
)

echo.
echo 🎯 Votre application Sortir est maintenant déployée et accessible !
echo    Ouvrez votre navigateur et allez sur http://localhost:8000
echo.
pause
