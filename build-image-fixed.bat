@echo off
echo 🏗️ Construction de l'image Docker corrigée
echo ==========================================

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

REM 2. Nettoyer les anciennes images
echo 🧹 Nettoyage des anciennes images...
docker rmi sortir-app:latest 2>nul

REM 3. Construire l'image avec le Dockerfile corrigé
echo 🏗️ Construction de l'image avec le Dockerfile corrigé...
docker build -f docker/Dockerfile.prod -t sortir-app:latest .

if %errorlevel% neq 0 (
    echo ❌ Erreur lors de la construction de l'image
    echo    Vérifiez les logs ci-dessus pour plus de détails
    pause
    exit /b 1
)

echo ✅ Image construite avec succès!

REM 4. Afficher les informations de l'image
echo 📊 Informations de l'image...
docker images sortir-app

echo.
echo 🎉 Image Docker créée avec succès!
echo.
echo 📋 Prochaines étapes :
echo    1. Tester l'image : test-docker.bat
echo    2. Déployer complètement : deploy-docker.bat
echo    3. Gérer l'application : manage-docker.bat
echo.
pause
