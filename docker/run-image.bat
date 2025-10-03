@echo off
echo 🚀 Lancement de l'image Docker Sortir...

REM 1. Vérifier que l'image existe
docker images sortir-app:latest >nul 2>&1
if %errorlevel% neq 0 (
    echo ❌ Image sortir-app:latest non trouvée
    echo    Exécutez d'abord : docker\build-image.bat
    pause
    exit /b 1
)

REM 2. Arrêter les conteneurs existants
echo 🛑 Arrêt des conteneurs existants...
docker stop sortir-container 2>nul
docker rm sortir-container 2>nul

REM 3. Lancer l'image
echo 🚀 Lancement de l'image...
docker run -d --name sortir-container -p 8000:8000 sortir-app:latest

if %errorlevel% neq 0 (
    echo ❌ Erreur lors du lancement de l'image
    pause
    exit /b 1
)

REM 4. Attendre que l'application démarre
echo ⏳ Attente du démarrage...
timeout /t 10 /nobreak >nul

REM 5. Vérifier l'état
echo 📊 État du conteneur...
docker ps --filter name=sortir-container

REM 6. Afficher les logs
echo 📝 Logs de l'application...
docker logs sortir-container

echo.
echo 🎉 Application lancée avec succès!
echo 🌐 Accessible sur http://localhost:8000
echo.
echo 📋 Commandes utiles :
echo    - docker logs -f sortir-container  : Voir les logs en temps réel
echo    - docker exec -it sortir-container bash  : Accéder au conteneur
echo    - docker stop sortir-container     : Arrêter l'application
echo    - docker rm sortir-container       : Supprimer le conteneur
echo.
pause

