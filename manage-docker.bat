@echo off
echo 🎮 Gestionnaire Docker - Application Sortir
echo ===========================================

:menu
echo.
echo Choisissez une action :
echo 1. 🚀 Démarrer l'application
echo 2. 🛑 Arrêter l'application
echo 3. 🔄 Redémarrer l'application
echo 4. 📊 Voir l'état des conteneurs
echo 5. 📝 Voir les logs de l'application
echo 6. 🗄️ Voir les logs de la base de données
echo 7. 🔧 Accéder au conteneur de l'application
echo 8. 🗄️ Accéder à MySQL
echo 9. 🧹 Nettoyer tout
echo 0. ❌ Quitter
echo.
set /p choice="Votre choix (0-9) : "

if "%choice%"=="1" goto start
if "%choice%"=="2" goto stop
if "%choice%"=="3" goto restart
if "%choice%"=="4" goto status
if "%choice%"=="5" goto logs_app
if "%choice%"=="6" goto logs_db
if "%choice%"=="7" goto access_app
if "%choice%"=="8" goto access_db
if "%choice%"=="9" goto cleanup
if "%choice%"=="0" goto exit
goto menu

:start
echo 🚀 Démarrage de l'application...
docker start sortir-database
timeout /t 5 /nobreak >nul
docker start sortir-container
echo ✅ Application démarrée
echo 🌐 Accessible sur http://localhost:8000
pause
goto menu

:stop
echo 🛑 Arrêt de l'application...
docker stop sortir-container sortir-database
echo ✅ Application arrêtée
pause
goto menu

:restart
echo 🔄 Redémarrage de l'application...
docker restart sortir-database
timeout /t 5 /nobreak >nul
docker restart sortir-container
echo ✅ Application redémarrée
echo 🌐 Accessible sur http://localhost:8000
pause
goto menu

:status
echo 📊 État des conteneurs...
docker ps --filter name=sortir
echo.
echo 📊 Utilisation des ressources...
docker stats --no-stream --format "table {{.Container}}\t{{.CPUPerc}}\t{{.MemUsage}}\t{{.NetIO}}\t{{.BlockIO}}" sortir-container sortir-database 2>nul
pause
goto menu

:logs_app
echo 📝 Logs de l'application (Ctrl+C pour quitter)...
docker logs -f sortir-container
pause
goto menu

:logs_db
echo 🗄️ Logs de la base de données (Ctrl+C pour quitter)...
docker logs -f sortir-database
pause
goto menu

:access_app
echo 🔧 Accès au conteneur de l'application...
echo    Tapez 'exit' pour quitter
docker exec -it sortir-container bash
pause
goto menu

:access_db
echo 🗄️ Accès à MySQL...
echo    Mot de passe root : rootpassword
echo    Tapez 'exit' pour quitter
docker exec -it sortir-database mysql -u root -p
pause
goto menu

:cleanup
echo 🧹 Nettoyage complet...
echo ⚠️  Cette action va supprimer tous les conteneurs et données !
set /p confirm="Êtes-vous sûr ? (oui/non) : "
if /i "%confirm%"=="oui" (
    docker stop sortir-container sortir-database 2>nul
    docker rm sortir-container sortir-database 2>nul
    docker rmi sortir-app:latest 2>nul
    echo ✅ Nettoyage terminé
) else (
    echo ❌ Nettoyage annulé
)
pause
goto menu

:exit
echo 👋 Au revoir !
exit /b 0
