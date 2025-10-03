#!/bin/bash

echo "🏗️ Construction de l'image Docker pour l'application Sortir..."

# 1. Vérifier que Docker est installé
if ! command -v docker &> /dev/null; then
    echo "❌ Docker n'est pas installé"
    exit 1
fi

echo "✅ Docker est disponible"

# 2. Construire l'image
echo "🏗️ Construction de l'image..."
docker build -f docker/Dockerfile -t sortir-app:latest .

if [ $? -ne 0 ]; then
    echo "❌ Erreur lors de la construction de l'image"
    exit 1
fi

echo "✅ Image construite avec succès!"

# 3. Afficher les informations de l'image
echo "📊 Informations de l'image..."
docker images sortir-app

echo ""
echo "🎉 Image Docker créée avec succès!"
echo ""
echo "📋 Commandes utiles :"
echo "   - docker run -p 8000:8000 sortir-app:latest"
echo "   - docker images sortir-app"
echo "   - docker rmi sortir-app:latest"
echo ""

