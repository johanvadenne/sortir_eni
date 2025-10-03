#!/bin/bash

echo "🔧 Correction sans Doctrine Fixtures"
echo "===================================="

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

# 1. Arrêter le conteneur actuel
print_status "Arrêt du conteneur actuel..."
docker stop $(docker ps -q --filter ancestor=sortir-app:latest) 2>/dev/null || true
docker rm $(docker ps -aq --filter ancestor=sortir-app:latest) 2>/dev/null || true

# 2. Reconstruire l'image sans fixtures
print_status "Reconstruction de l'image sans fixtures..."
docker build -f docker/Dockerfile.prod-no-fixtures -t sortir-app:latest .

if [ $? -ne 0 ]; then
    print_error "Erreur lors de la reconstruction"
    exit 1
fi

print_success "Image reconstruite avec succès"

# 3. Relancer l'application
print_status "Relancement de l'application..."
docker run -d --name sortir-container \
    --link sortir-database:database \
    -e DATABASE_URL="mysql://sortir_user:sortir_password@database:3306/sortir?serverVersion=8.0" \
    -e APP_ENV=prod \
    -e APP_SECRET=your_secret_key_here \
    -p 8000:8000 \
    sortir-app:latest

if [ $? -ne 0 ]; then
    print_error "Erreur lors du relancement"
    exit 1
fi

# 4. Attendre le démarrage
print_status "Attente du démarrage (15 secondes)..."
sleep 15

# 5. Vérifier les logs
print_status "Vérification des logs..."
docker logs --tail 10 sortir-container

# 6. Test de l'application
print_status "Test de l'application..."
if curl -s -o /dev/null -w "%{http_code}" http://localhost:8000 | grep -q "200\|302"; then
    print_success "Application accessible et fonctionnelle"
    echo ""
    echo -e "${GREEN}🎉${NC} Correction terminée avec succès!"
    echo "🌐 Application accessible sur : http://localhost:8000"
    echo "ℹ️  Note: Les fixtures ne sont pas disponibles en production"
else
    print_error "Application non accessible"
    echo "Logs détaillés :"
    docker logs sortir-container
fi

echo ""
