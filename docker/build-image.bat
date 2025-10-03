@echo off
echo 🏗️ Construction de l'image Docker pour l'application Sortir...

REM 1. Vérifier que Docker est installé
docker --version >nul 2>&1
if %errorlevel% neq 0 (
    echo ❌ Docker n'est pas installé ou n'est pas dans le PATH
    echo    Veuillez installer Docker Desktop
    pause
    exit /b 1
)

echo ✅ Docker est disponible

REM 2. Construire l'image
echo 🏗️ Construction de l'image...
docker build -f docker/Dockerfile -t sortir-app:latest .

if %errorlevel% neq 0 (
    echo ❌ Erreur lors de la construction de l'image
    pause
    exit /b 1
)

echo ✅ Image construite avec succès!

REM 3. Afficher les informations de l'image
echo 📊 Informations de l'image...
docker images sortir-app

echo.
echo 🎉 Image Docker créée avec succès!
echo.
echo 📋 Commandes utiles :
echo    - docker run -p 8000:8000 sortir-app:latest
echo    - docker images sortir-app
echo    - docker rmi sortir-app:latest
echo.
pause

