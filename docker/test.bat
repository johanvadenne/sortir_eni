@echo off
echo 🧪 Test du déploiement Docker de l'application Sortir...

REM 1. Vérifier que Docker fonctionne
echo 📋 Test de Docker...
docker --version
if %errorlevel% neq 0 (
    echo ❌ Docker n'est pas accessible
    pause
    exit /b 1
)

REM 2. Vérifier que les conteneurs sont en cours d'exécution
echo 📊 Vérification des conteneurs...
docker-compose ps
if %errorlevel% neq 0 (
    echo ❌ Erreur lors de la vérification des conteneurs
    pause
    exit /b 1
)

REM 3. Tester la connexion à la base de données
echo 🗄️ Test de la base de données...
docker-compose exec database mysqladmin ping -h localhost -u root -prootpassword
if %errorlevel% neq 0 (
    echo ❌ Base de données non accessible
    pause
    exit /b 1
)

REM 4. Tester l'application
echo 🌐 Test de l'application...
curl -s -o nul -w "%%{http_code}" http://localhost:8000
if %errorlevel% neq 0 (
    echo ❌ Application non accessible
    pause
    exit /b 1
)

REM 5. Afficher les logs récents
echo 📝 Logs récents de l'application...
docker-compose logs --tail=20 app

echo.
echo 🎉 Tous les tests sont passés avec succès!
echo 🌐 Votre application est accessible sur http://localhost:8000
echo.
pause

