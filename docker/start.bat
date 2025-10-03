@echo off
echo 🚀 Démarrage de l'application Sortir avec Docker...

docker-compose up -d

echo ✅ Application démarrée!
echo 🌐 Accessible sur http://localhost:8000
echo.
echo 📋 Commandes utiles :
echo    - docker-compose logs -f app     : Voir les logs
echo    - docker-compose stop            : Arrêter
echo    - docker-compose restart         : Redémarrer
echo.
pause

