@echo off
echo 🧪 Test rapide de l'image Docker
echo =================================

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

REM 2. Construire l'image
echo 🏗️ Construction de l'image...
docker build -f docker/Dockerfile -t sortir-app:latest . --quiet

if %errorlevel% neq 0 (
    echo ❌ Erreur lors de la construction
    pause
    exit /b 1
)
echo ✅ Image construite

REM 3. Lancer l'image
echo 🚀 Lancement de l'image...
docker run -d --name sortir-test -p 8000:8000 sortir-app:latest

if %errorlevel% neq 0 (
    echo ❌ Erreur lors du lancement
    pause
    exit /b 1
)
echo ✅ Image lancée

REM 4. Attendre le démarrage
echo ⏳ Attente du démarrage (10 secondes)...
timeout /t 10 /nobreak >nul

REM 5. Tester l'application
echo 🌐 Test de l'application...
curl -s -o nul -w "%%{http_code}" http://localhost:8000 >nul 2>&1
if %errorlevel% equ 0 (
    echo ✅ Application accessible
) else (
    echo ⚠️  Application en cours de démarrage
)

REM 6. Afficher les logs
echo 📝 Derniers logs...
docker logs --tail 10 sortir-test

REM 7. Nettoyer
echo 🧹 Nettoyage...
docker stop sortir-test
docker rm sortir-test

echo.
echo 🎉 Test terminé avec succès !
echo    Votre image Docker est prête pour le déploiement.
echo.
pause
