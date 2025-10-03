@echo off
echo 🔍 Diagnostic des ports utilisés
echo =================================

echo 📋 Ports utilisés sur votre système :
echo.

echo 🔍 Port 8000 (Application Symfony) :
netstat -ano | findstr :8000
if %errorlevel% equ 0 (
    echo ⚠️  Port 8000 est utilisé par :
    for /f "tokens=5" %%a in ('netstat -ano ^| findstr :8000') do (
        echo    PID: %%a
        tasklist /FI "PID eq %%a" /FO TABLE /NH
    )
) else (
    echo ✅ Port 8000 est libre
)

echo.
echo 🔍 Port 3306 (MySQL) :
netstat -ano | findstr :3306
if %errorlevel% equ 0 (
    echo ⚠️  Port 3306 est utilisé par :
    for /f "tokens=5" %%a in ('netstat -ano ^| findstr :3306') do (
        echo    PID: %%a
        tasklist /FI "PID eq %%a" /FO TABLE /NH
    )
) else (
    echo ✅ Port 3306 est libre
)

echo.
echo 🔍 Port 80 (HTTP) :
netstat -ano | findstr :80
if %errorlevel% equ 0 (
    echo ⚠️  Port 80 est utilisé
) else (
    echo ✅ Port 80 est libre
)

echo.
echo 🔍 Port 443 (HTTPS) :
netstat -ano | findstr :443
if %errorlevel% equ 0 (
    echo ⚠️  Port 443 est utilisé
) else (
    echo ✅ Port 443 est libre
)

echo.
echo 📋 Conteneurs Docker en cours d'exécution :
docker ps -a 2>nul
if %errorlevel% neq 0 (
    echo ❌ Docker n'est pas accessible
) else (
    echo ✅ Docker est accessible
)

echo.
echo 🎯 Solutions recommandées :
echo    1. Utiliser des ports alternatifs (8001, 3307)
echo    2. Arrêter les services qui utilisent ces ports
echo    3. Utiliser le script deploy-free-ports.bat
echo.
pause
