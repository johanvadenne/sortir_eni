#!/bin/bash

echo "🚀 Lancement avec Docker Compose corrigé"
echo "========================================"

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

# 1. Arrêter tous les conteneurs existants
print_status "Arrêt des conteneurs existants..."
docker-compose down 2>/dev/null || true
docker stop $(docker ps -aq) 2>/dev/null || true
docker rm $(docker ps -aq) 2>/dev/null || true

# 2. Lancer avec Docker Compose
print_status "Lancement avec Docker Compose..."
docker-compose up -d --build

if [ $? -ne 0 ]; then
    print_error "Erreur lors du lancement avec Docker Compose"
    exit 1
fi

# 3. Attendre le démarrage
print_status "Attente du démarrage (60 secondes)..."
sleep 60

# 4. Vérifier l'état
print_status "État des conteneurs..."
docker-compose ps

# 5. Vérifier les logs de l'application
print_status "Logs de l'application..."
docker-compose logs --tail 10 app

# 6. Vérifier les logs de MySQL
print_status "Logs de MySQL..."
docker-compose logs --tail 5 database

# 7. Test de l'application
print_status "Test de l'application..."
HTTP_CODE=$(curl -s -o /dev/null -w "%{http_code}" http://localhost:8000)
if [ "$HTTP_CODE" = "200" ] || [ "$HTTP_CODE" = "302" ]; then
    print_success "Application accessible et fonctionnelle (HTTP $HTTP_CODE)"
    echo ""
    echo -e "${GREEN}🎉${NC} DÉPLOIEMENT AVEC DOCKER COMPOSE TERMINÉ AVEC SUCCÈS!"
    echo "🌐 Application accessible sur : http://localhost:8000"
    echo "🗄️ Base de données MySQL sur : localhost:3306"
    echo ""
    echo "📋 Commandes utiles :"
    echo "   - docker-compose logs -f app"
    echo "   - docker-compose logs -f database"
    echo "   - docker-compose down"
    echo "   - docker-compose restart"
    echo "   - docker-compose ps"
else
    print_error "Application non accessible (HTTP $HTTP_CODE)"
    echo "Logs détaillés de l'application :"
    docker-compose logs app
fi

echo ""
