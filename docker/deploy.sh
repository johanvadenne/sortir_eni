#!/bin/bash

echo "🐳 Déploiement Docker de l'application Sortir..."

# 1. Vérifier que Docker est installé
echo "📋 Vérification de Docker..."
if ! command -v docker &> /dev/null; then
    echo "❌ Docker n'est pas installé"
    exit 1
fi

if ! command -v docker-compose &> /dev/null; then
    echo "❌ Docker Compose n'est pas installé"
    exit 1
fi

echo "✅ Docker est installé"

# 2. Créer le fichier .env si il n'existe pas
if [ ! -f .env ]; then
    echo "⚙️ Création du fichier .env..."
    cp docker/env_docker_template.txt .env
    echo "⚠️ Fichier .env créé. Veuillez modifier les valeurs selon vos besoins."
    echo "   Notamment : APP_SECRET, MYSQL_PASSWORD, etc."
    read -p "Appuyez sur Entrée pour continuer..."
fi

# 3. Arrêter les conteneurs existants
echo "🛑 Arrêt des conteneurs existants..."
docker-compose down

# 4. Construire et démarrer les conteneurs
echo "🏗️ Construction et démarrage des conteneurs..."
docker-compose up --build -d

# 5. Attendre que les services soient prêts
echo "⏳ Attente du démarrage des services..."
sleep 30

# 6. Vérifier l'état des conteneurs
echo "📊 Vérification de l'état des conteneurs..."
docker-compose ps

# 7. Afficher les logs
echo "📝 Logs de l'application..."
docker-compose logs app

echo ""
echo "🎉 Déploiement Docker terminé!"
echo ""
echo "🌐 Votre application est accessible sur :"
echo "   - http://localhost:8000 (Application Symfony)"
echo "   - http://localhost (Nginx - si activé)"
echo ""
echo "📋 Commandes utiles :"
echo "   - docker-compose logs -f app     : Voir les logs en temps réel"
echo "   - docker-compose exec app bash   : Accéder au conteneur"
echo "   - docker-compose down            : Arrêter les conteneurs"
echo "   - docker-compose restart         : Redémarrer les conteneurs"
echo ""

