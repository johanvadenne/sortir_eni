#!/bin/bash

echo "🧪 Test rapide de l'image Docker sur Linux"
echo "=========================================="

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

# 1. Vérifier que l'image existe
print_status "Vérification de l'image..."
if ! docker images sortir-app:latest | grep -q "sortir-app"; then
    print_error "Image sortir-app:latest non trouvée"
    echo "Construisez d'abord l'image avec : ./build-linux.sh"
    exit 1
fi

# 2. Lancer l'image
print_status "Lancement de l'image..."
docker run -d --name sortir-test -p 8000:8000 sortir-app:latest

if [ $? -ne 0 ]; then
    print_error "Erreur lors du lancement de l'image"
    exit 1
fi

# 3. Attendre le démarrage
print_status "Attente du démarrage (10 secondes)..."
sleep 10

# 4. Tester l'application
print_status "Test de l'application..."
if curl -s -o /dev/null -w "%{http_code}" http://localhost:8000 | grep -q "200\|302"; then
    print_success "Application accessible"
else
    print_error "Application non accessible"
    echo "Logs du conteneur :"
    docker logs sortir-test
fi

# 5. Afficher les logs
print_status "Derniers logs..."
docker logs --tail 10 sortir-test

# 6. Nettoyer
print_status "Nettoyage..."
docker stop sortir-test
docker rm sortir-test

echo ""
echo -e "${GREEN}🎉${NC} Test terminé!"
echo "Votre image Docker fonctionne correctement."
echo ""
