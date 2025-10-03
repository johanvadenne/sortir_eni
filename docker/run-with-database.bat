@echo off
echo 🚀 Lancement de l'application Sortir avec base de données MySQL...

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
docker stop sortir-container sortir-database 2>nul
docker rm sortir-container sortir-database 2>nul

REM 3. Lancer la base de données MySQL
echo 🗄️ Lancement de MySQL...
docker run -d --name sortir-database \
    -e MYSQL_ROOT_PASSWORD=rootpassword \
    -e MYSQL_DATABASE=sortir \
    -e MYSQL_USER=sortir_user \
    -e MYSQL_PASSWORD=sortir_password \
    -p 3306:3306 \
    mysql:8.0

if %errorlevel% neq 0 (
    echo ❌ Erreur lors du lancement de MySQL
    pause
    exit /b 1
)

REM 4. Attendre que MySQL démarre
echo ⏳ Attente du démarrage de MySQL...
timeout /t 30 /nobreak >nul

REM 5. Lancer l'application
echo 🚀 Lancement de l'application...
docker run -d --name sortir-container \
    --link sortir-database:database \
    -e DATABASE_URL="mysql://sortir_user:sortir_password@database:3306/sortir?serverVersion=8.0" \
    -p 8000:8000 \
    sortir-app:latest

if %errorlevel% neq 0 (
    echo ❌ Erreur lors du lancement de l'application
    pause
    exit /b 1
)

REM 6. Attendre que l'application démarre
echo ⏳ Attente du démarrage de l'application...
timeout /t 15 /nobreak >nul

REM 7. Vérifier l'état
echo 📊 État des conteneurs...
docker ps --filter name=sortir

REM 8. Afficher les logs
echo 📝 Logs de l'application...
docker logs sortir-container

echo.
echo 🎉 Application lancée avec succès!
echo 🌐 Accessible sur http://localhost:8000
echo 🗄️ Base de données MySQL sur localhost:3306
echo.
echo 📋 Commandes utiles :
echo    - docker logs -f sortir-container  : Voir les logs de l'app
echo    - docker logs -f sortir-database   : Voir les logs de MySQL
echo    - docker exec -it sortir-container bash  : Accéder à l'app
echo    - docker exec -it sortir-database mysql -u root -p  : Accéder à MySQL
echo    - docker stop sortir-container sortir-database  : Arrêter tout
echo.
pause

