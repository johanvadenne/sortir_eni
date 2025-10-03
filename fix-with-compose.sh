#!/bin/bash

echo "🔧 Correction avec Docker Compose"
echo "================================="

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

# 1. Arrêter tous les conteneurs
print_status "Arrêt de tous les conteneurs..."
docker-compose down 2>/dev/null || true
docker stop $(docker ps -aq) 2>/dev/null || true
docker rm $(docker ps -aq) 2>/dev/null || true

# 2. Supprimer le fichier de configuration fixtures
print_status "Suppression de la configuration fixtures..."
if [ -f "config/packages/doctrine_fixtures.yaml" ]; then
    rm config/packages/doctrine_fixtures.yaml
    print_success "Fichier doctrine_fixtures.yaml supprimé"
else
    print_status "Fichier doctrine_fixtures.yaml déjà supprimé"
fi

# 3. Lancer avec Docker Compose corrigé
print_status "Lancement avec Docker Compose corrigé..."
docker-compose -f compose-fixed.yaml up -d --build

if [ $? -ne 0 ]; then
    print_error "Erreur lors du lancement avec Docker Compose"
    exit 1
fi

# 4. Attendre le démarrage
print_status "Attente du démarrage (30 secondes)..."
sleep 30

# 5. Vérifier l'état
print_status "État des conteneurs..."
docker-compose -f compose-fixed.yaml ps

# 6. Vérifier les logs
print_status "Logs de l'application..."
docker-compose -f compose-fixed.yaml logs --tail 10 app

# 7. Test de l'application
print_status "Test de l'application..."
HTTP_CODE=$(curl -s -o /dev/null -w "%{http_code}" http://localhost:8000)
if [ "$HTTP_CODE" = "200" ] || [ "$HTTP_CODE" = "302" ]; then
    print_success "Application accessible et fonctionnelle (HTTP $HTTP_CODE)"
    echo ""
    echo -e "${GREEN}🎉${NC} Correction terminée avec succès!"
    echo "🌐 Application accessible sur : http://localhost:8000"
    echo "🗄️ Base de données MySQL sur : localhost:3306"
    echo ""
    echo "📋 Commandes utiles :"
    echo "   - docker-compose -f compose-fixed.yaml logs -f"
    echo "   - docker-compose -f compose-fixed.yaml down"
    echo "   - docker-compose -f compose-fixed.yaml restart"
else
    print_error "Application non accessible (HTTP $HTTP_CODE)"
    echo "Logs détaillés :"
    docker-compose -f compose-fixed.yaml logs app
fi

echo ""
