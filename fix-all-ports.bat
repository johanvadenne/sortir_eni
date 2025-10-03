@echo off
echo 🔧 Correction de tous les ports occupés
echo =======================================

echo 📋 Vérification des ports utilisés...

REM Vérifier le port 8000
echo 🔍 Port 8000 (Application) :
netstat -ano | findstr :8000
if %errorlevel% equ 0 (
    echo ⚠️  Port 8000 est utilisé
) else (
    echo ✅ Port 8000 est libre
)

echo.
echo 🔍 Port 3306 (MySQL) :
netstat -ano | findstr :3306
if %errorlevel% equ 0 (
    echo ⚠️  Port 3306 est utilisé
) else (
    echo ✅ Port 3306 est libre
)

echo.
echo 🛑 Arrêt de tous les conteneurs Docker...
docker stop $(docker ps -aq) 2>nul
docker rm $(docker ps -aq) 2>nul

echo 🛑 Arrêt de Docker Compose...
docker-compose down 2>nul

echo.
echo 📋 Ports libérés. Vous pouvez maintenant :
echo    1. Utiliser des ports alternatifs
echo    2. Arrêter les services qui utilisent ces ports
echo    3. Relancer le déploiement
echo.
pause
