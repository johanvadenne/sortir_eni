#!/bin/bash

echo "🔧 Correction rapide - Suppression de la config fixtures"
echo "======================================================="

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
CONTAINER_ID=$(docker ps -q --filter ancestor=sortir-app:latest)
if [ ! -z "$CONTAINER_ID" ]; then
    docker stop $CONTAINER_ID
    docker rm $CONTAINER_ID
    print_success "Conteneur arrêté et supprimé"
else
    print_status "Aucun conteneur à arrêter"
fi

# 2. Supprimer le fichier de configuration fixtures
print_status "Suppression de la configuration fixtures..."
if [ -f "config/packages/doctrine_fixtures.yaml" ]; then
    rm config/packages/doctrine_fixtures.yaml
    print_success "Fichier doctrine_fixtures.yaml supprimé"
else
    print_status "Fichier doctrine_fixtures.yaml déjà supprimé"
fi

# 3. Reconstruire l'image
print_status "Reconstruction de l'image..."
docker build -f docker/Dockerfile.prod -t sortir-app:latest .

if [ $? -ne 0 ]; then
    print_error "Erreur lors de la reconstruction"
    exit 1
fi

print_success "Image reconstruite avec succès"

# 4. Relancer l'application
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

# 5. Attendre le démarrage
print_status "Attente du démarrage (15 secondes)..."
sleep 15

# 6. Vérifier les logs
print_status "Vérification des logs..."
docker logs --tail 10 sortir-container

# 7. Test de l'application
print_status "Test de l'application..."
HTTP_CODE=$(curl -s -o /dev/null -w "%{http_code}" http://localhost:8000)
if [ "$HTTP_CODE" = "200" ] || [ "$HTTP_CODE" = "302" ]; then
    print_success "Application accessible et fonctionnelle (HTTP $HTTP_CODE)"
    echo ""
    echo -e "${GREEN}🎉${NC} Correction terminée avec succès!"
    echo "🌐 Application accessible sur : http://localhost:8000"
    echo "ℹ️  Note: Les fixtures ne sont pas disponibles (supprimées pour éviter les erreurs)"
else
    print_error "Application non accessible (HTTP $HTTP_CODE)"
    echo "Logs détaillés :"
    docker logs sortir-container
fi

echo ""
