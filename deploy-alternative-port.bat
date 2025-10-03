@echo off
echo 🚀 Déploiement avec port alternatif (8001)
echo ===========================================

REM 1. Vérifier Docker
echo 📋 Vérification de Docker...
docker --version >nul 2>&1
if %errorlevel% neq 0 (
    echo ❌ Docker n'est pas disponible
    pause
    exit /b 1
)

docker info >nul 2>&1
if %errorlevel% neq 0 (
    echo ❌ Docker Desktop n'est pas démarré
    echo    Veuillez démarrer Docker Desktop et relancer ce script
    pause
    exit /b 1
)
echo ✅ Docker est prêt

REM 2. Nettoyer les anciens conteneurs
echo 🧹 Nettoyage des anciens conteneurs...
docker stop sortir-app-1 sortir-container sortir-database 2>nul
docker rm sortir-app-1 sortir-container sortir-database 2>nul
docker-compose down 2>nul

REM 3. Construire l'image
echo 🏗️ Construction de l'image...
docker build -f docker/Dockerfile.prod -t sortir-app:latest .

if %errorlevel% neq 0 (
    echo ❌ Erreur lors de la construction
    pause
    exit /b 1
)
echo ✅ Image construite

REM 4. Lancer avec Docker Compose sur le port 8001
echo 🚀 Lancement sur le port 8001...
docker-compose -f compose-port-8001.yaml up -d

if %errorlevel% neq 0 (
    echo ❌ Erreur lors du lancement
    pause
    exit /b 1
)

REM 5. Attendre le démarrage
echo ⏳ Attente du démarrage (30 secondes)...
timeout /t 30 /nobreak >nul

REM 6. Vérifier l'état
echo 📊 État des conteneurs...
docker ps --filter name=sortir

echo.
echo 🎉 DÉPLOIEMENT TERMINÉ AVEC SUCCÈS!
echo ================================================
echo.
echo 🌐 Application accessible sur : http://localhost:8001
echo 🗄️ Base de données MySQL sur : localhost:3306
echo.
echo 📋 Commandes utiles :
echo    - docker-compose -f compose-port-8001.yaml logs -f
echo    - docker-compose -f compose-port-8001.yaml down
echo    - docker-compose -f compose-port-8001.yaml restart
echo.
pause
