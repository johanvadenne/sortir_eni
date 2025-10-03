@echo off
echo 🚀 Déploiement avec ports libres
echo =================================

REM 1. Vérifier Docker
echo 📋 Vérification de Docker...
docker --version >nul 2>&1
if %errorlevel% neq 0 (
    echo ❌ Docker n'est pas disponible
    pause
    exit /b 1
)

docker info >nul 2>&1
if %errorlevel% neq 0 (
    echo ❌ Docker Desktop n'est pas démarré
    echo    Veuillez démarrer Docker Desktop et relancer ce script
    pause
    exit /b 1
)
echo ✅ Docker est prêt

REM 2. Nettoyer tout
echo 🧹 Nettoyage complet...
docker stop $(docker ps -aq) 2>nul
docker rm $(docker ps -aq) 2>nul
docker-compose down 2>nul

REM 3. Construire l'image
echo 🏗️ Construction de l'image...
docker build -f docker/Dockerfile.prod -t sortir-app:latest .

if %errorlevel% neq 0 (
    echo ❌ Erreur lors de la construction
    pause
    exit /b 1
)
echo ✅ Image construite

REM 4. Lancer MySQL sur le port 3307
echo 🗄️ Lancement de MySQL sur le port 3307...
docker run -d --name sortir-database \
    -e MYSQL_ROOT_PASSWORD=rootpassword \
    -e MYSQL_DATABASE=sortir \
    -e MYSQL_USER=sortir_user \
    -e MYSQL_PASSWORD=sortir_password \
    -p 3307:3306 \
    mysql:8.0

if %errorlevel% neq 0 (
    echo ❌ Erreur lors du lancement de MySQL
    pause
    exit /b 1
)
echo ✅ MySQL démarré sur le port 3307

REM 5. Attendre MySQL
echo ⏳ Attente du démarrage de MySQL (30 secondes)...
timeout /t 30 /nobreak >nul

REM 6. Lancer l'application sur le port 8001
echo 🚀 Lancement de l'application sur le port 8001...
docker run -d --name sortir-container \
    --link sortir-database:database \
    -e DATABASE_URL="mysql://sortir_user:sortir_password@database:3306/sortir?serverVersion=8.0" \
    -e APP_ENV=prod \
    -e APP_SECRET=your_secret_key_here \
    -p 8001:8000 \
    sortir-app:latest

if %errorlevel% neq 0 (
    echo ❌ Erreur lors du lancement de l'application
    pause
    exit /b 1
)
echo ✅ Application démarrée sur le port 8001

REM 7. Attendre l'application
echo ⏳ Attente du démarrage de l'application (15 secondes)...
timeout /t 15 /nobreak >nul

REM 8. Vérifier l'état
echo 📊 État des conteneurs...
docker ps --filter name=sortir

echo.
echo 🎉 DÉPLOIEMENT TERMINÉ AVEC SUCCÈS!
echo ================================================
echo.
echo 🌐 Application accessible sur : http://localhost:8001
echo 🗄️ Base de données MySQL sur : localhost:3307
echo.
echo 📋 Informations de connexion :
echo    - Utilisateur MySQL : sortir_user
echo    - Mot de passe MySQL : sortir_password
echo    - Base de données : sortir
echo    - Root MySQL : rootpassword
echo.
echo 📋 Commandes utiles :
echo    - docker logs -f sortir-container  : Voir les logs de l'app
echo    - docker logs -f sortir-database   : Voir les logs de MySQL
echo    - docker exec -it sortir-container bash  : Accéder à l'app
echo    - docker stop sortir-container sortir-database  : Arrêter l'application
echo.
echo 🔧 Pour arrêter l'application : docker stop sortir-container sortir-database
echo 🔄 Pour redémarrer l'application : docker start sortir-container sortir-database
echo.

REM 9. Test de l'application
echo 🧪 Test de l'application...
curl -s -o nul -w "%%{http_code}" http://localhost:8001 >nul 2>&1
if %errorlevel% equ 0 (
    echo ✅ Application accessible et fonctionnelle
) else (
    echo ⚠️  Application en cours de démarrage, veuillez patienter...
)

echo.
echo 🎯 Votre application Sortir est maintenant déployée et accessible !
echo    Ouvrez votre navigateur et allez sur http://localhost:8001
echo.
pause
