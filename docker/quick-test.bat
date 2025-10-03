@echo off
echo 🧪 Test rapide de l'image Docker Sortir...

REM 1. Construire l'image
echo 🏗️ Construction de l'image...
docker build -f docker/Dockerfile -t sortir-app:latest . --quiet

if %errorlevel% neq 0 (
    echo ❌ Erreur lors de la construction
    pause
    exit /b 1
)

REM 2. Lancer l'image
echo 🚀 Lancement de l'image...
docker run -d --name sortir-test -p 8000:8000 sortir-app:latest

if %errorlevel% neq 0 (
    echo ❌ Erreur lors du lancement
    pause
    exit /b 1
)

REM 3. Attendre le démarrage
echo ⏳ Attente du démarrage...
timeout /t 10 /nobreak >nul

REM 4. Tester l'application
echo 🌐 Test de l'application...
curl -s -o nul -w "%%{http_code}" http://localhost:8000

if %errorlevel% neq 0 (
    echo ❌ Application non accessible
    docker logs sortir-test
    docker stop sortir-test
    docker rm sortir-test
    pause
    exit /b 1
)

REM 5. Nettoyer
echo 🧹 Nettoyage...
docker stop sortir-test
docker rm sortir-test

echo.
echo 🎉 Test réussi! L'image fonctionne correctement.
echo 🌐 Votre application est prête à être déployée.
echo.
pause

