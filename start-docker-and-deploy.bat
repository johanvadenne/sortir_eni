@echo off
echo 🚀 Démarrage de Docker Desktop et Déploiement
echo ===============================================

REM 1. Vérifier si Docker Desktop est déjà démarré
echo 📋 Vérification de Docker Desktop...
docker info >nul 2>&1
if %errorlevel% equ 0 (
    echo ✅ Docker Desktop est déjà démarré
    goto deploy
)

REM 2. Démarrer Docker Desktop
echo 🚀 Démarrage de Docker Desktop...
start "" "C:\Program Files\Docker\Docker\Docker Desktop.exe"

REM 3. Attendre que Docker Desktop démarre
echo ⏳ Attente du démarrage de Docker Desktop...
echo    Cela peut prendre 1-2 minutes...

:wait_loop
timeout /t 10 /nobreak >nul
docker info >nul 2>&1
if %errorlevel% equ 0 (
    echo ✅ Docker Desktop est maintenant démarré
    goto deploy
) else (
    echo ⏳ Docker Desktop démarre encore... (attente de 10 secondes)
    goto wait_loop
)

:deploy
echo.
echo 🎯 Docker Desktop est prêt, lancement du déploiement...
echo.
call deploy-docker.bat
