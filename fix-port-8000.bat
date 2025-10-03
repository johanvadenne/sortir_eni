@echo off
echo 🔧 Correction du problème de port 8000
echo ======================================

echo 📋 Vérification des conteneurs utilisant le port 8000...

REM Vérifier si Docker Desktop est démarré
docker ps >nul 2>&1
if %errorlevel% neq 0 (
    echo ❌ Docker Desktop n'est pas démarré
    echo    Veuillez démarrer Docker Desktop et relancer ce script
    pause
    exit /b 1
)

echo ✅ Docker Desktop est démarré

REM Arrêter tous les conteneurs qui pourraient utiliser le port 8000
echo 🛑 Arrêt des conteneurs existants...
docker stop sortir-app-1 sortir-container 2>nul
docker rm sortir-app-1 sortir-container 2>nul

REM Arrêter Docker Compose si il tourne
echo 🛑 Arrêt de Docker Compose...
docker-compose down 2>nul

REM Vérifier les processus utilisant le port 8000
echo 📋 Vérification du port 8000...
netstat -ano | findstr :8000

echo.
echo ✅ Nettoyage terminé
echo.
echo 🚀 Vous pouvez maintenant relancer :
echo    - docker-compose up -d
echo    - ou deploy-docker.bat
echo.
pause
