@echo off
echo 🐳 Déploiement Docker de l'application Sortir...

REM 1. Vérifier que Docker est installé
echo 📋 Vérification de Docker...
docker --version >nul 2>&1
if %errorlevel% neq 0 (
    echo ❌ Docker n'est pas installé ou n'est pas dans le PATH
    echo    Veuillez installer Docker Desktop
    pause
    exit /b 1
)

docker-compose --version >nul 2>&1
if %errorlevel% neq 0 (
    echo ❌ Docker Compose n'est pas installé
    pause
    exit /b 1
)

echo ✅ Docker est installé

REM 2. Créer le fichier .env si il n'existe pas
if not exist .env (
    echo ⚙️ Création du fichier .env...
    copy docker\env_docker_template.txt .env
    echo ⚠️ Fichier .env créé. Veuillez modifier les valeurs selon vos besoins.
    echo    Notamment : APP_SECRET, MYSQL_PASSWORD, etc.
    pause
)

REM 3. Arrêter les conteneurs existants
echo 🛑 Arrêt des conteneurs existants...
docker-compose down

REM 4. Construire et démarrer les conteneurs
echo 🏗️ Construction et démarrage des conteneurs...
docker-compose up --build -d

REM 5. Attendre que les services soient prêts
echo ⏳ Attente du démarrage des services...
timeout /t 30 /nobreak >nul

REM 6. Vérifier l'état des conteneurs
echo 📊 Vérification de l'état des conteneurs...
docker-compose ps

REM 7. Afficher les logs
echo 📝 Logs de l'application...
docker-compose logs app

echo.
echo 🎉 Déploiement Docker terminé!
echo.
echo 🌐 Votre application est accessible sur :
echo    - http://localhost:8000 (Application Symfony)
echo    - http://localhost (Nginx - si activé)
echo.
echo 📋 Commandes utiles :
echo    - docker-compose logs -f app     : Voir les logs en temps réel
echo    - docker-compose exec app bash   : Accéder au conteneur
echo    - docker-compose down            : Arrêter les conteneurs
echo    - docker-compose restart         : Redémarrer les conteneurs
echo.
pause

