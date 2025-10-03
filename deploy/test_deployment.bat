@echo off
echo 🧪 Test du déploiement de l'application Sortir...

REM 1. Tester la connexion à la base de données
echo 📊 Test de la connexion à la base de données...
php bin/console doctrine:database:create --env=prod --if-not-exists
if %errorlevel% neq 0 (
    echo ❌ Erreur de connexion à la base de données
    pause
    exit /b 1
)
echo ✅ Connexion à la base de données OK

REM 2. Tester les migrations
echo 🔄 Test des migrations...
php bin/console doctrine:migrations:migrate --env=prod --no-interaction
if %errorlevel% neq 0 (
    echo ❌ Erreur lors des migrations
    pause
    exit /b 1
)
echo ✅ Migrations OK

REM 3. Tester le cache
echo 🧹 Test du cache...
php bin/console cache:clear --env=prod
if %errorlevel% neq 0 (
    echo ❌ Erreur lors du nettoyage du cache
    pause
    exit /b 1
)
echo ✅ Cache OK

REM 4. Tester les routes
echo 🛣️ Test des routes...
php bin/console debug:router --env=prod > nul 2>&1
if %errorlevel% neq 0 (
    echo ❌ Erreur lors du test des routes
    pause
    exit /b 1
)
echo ✅ Routes OK

REM 5. Tester l'application
echo 🌐 Test de l'application...
php bin/console about --env=prod > nul 2>&1
if %errorlevel% neq 0 (
    echo ❌ Erreur lors du test de l'application
    pause
    exit /b 1
)
echo ✅ Application OK

echo.
echo 🎉 Tous les tests sont passés avec succès!
echo 🌐 Votre application est prête à être utilisée
echo.
echo 📋 Prochaines étapes:
echo    1. Configurez votre serveur web (Apache/Nginx)
echo    2. Testez l'application dans votre navigateur
echo    3. Configurez les logs de production
echo.
pause

