#!/bin/bash

echo "🏗️ Construction de l'image Docker pour Linux"
echo "============================================="

# Couleurs
GREEN='\033[0;32m'
RED='\033[0;31m'
BLUE='\033[0;34m'
NC='\033[0m'

print_status() {
    echo -e "${BLUE}📋${NC} $1"
}

print_success() {
    echo -e "${GREEN}✅${NC} $1"
}

print_error() {
    echo -e "${RED}❌${NC} $1"
}

# 1. Vérifier Docker
print_status "Vérification de Docker..."
if ! command -v docker &> /dev/null; then
    print_error "Docker n'est pas installé"
    echo "Installez Docker avec : sudo apt-get install docker.io"
    exit 1
fi

if ! docker info &> /dev/null; then
    print_error "Docker n'est pas démarré"
    echo "Démarrez Docker avec : sudo systemctl start docker"
    exit 1
fi

print_success "Docker est prêt"

# 2. Nettoyer les anciennes images
print_status "Nettoyage des anciennes images..."
docker rmi sortir-app:latest 2>/dev/null || true

# 3. Construire l'image
print_status "Construction de l'image avec PHP 8.3..."
docker build -f docker/Dockerfile.prod -t sortir-app:latest .

if [ $? -ne 0 ]; then
    print_error "Erreur lors de la construction de l'image"
    echo "Vérifiez les logs ci-dessus pour plus de détails"
    exit 1
fi

print_success "Image construite avec succès!"

# 4. Afficher les informations de l'image
print_status "Informations de l'image..."
docker images sortir-app

echo ""
echo -e "${GREEN}🎉${NC} Image Docker créée avec succès!"
echo ""
echo -e "${BLUE}📋${NC} Prochaines étapes :"
echo "   1. Tester l'image : ./test-linux.sh"
echo "   2. Déployer complètement : ./deploy-linux.sh"
echo "   3. Lancer avec Docker Compose : docker-compose up -d"
echo ""
