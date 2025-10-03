#!/bin/bash

echo "🔧 Build Docker corrigé pour Linux"
echo "==================================="

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

# 1. Arrêter les conteneurs existants
print_status "Arrêt des conteneurs existants..."
docker stop $(docker ps -aq) 2>/dev/null || true
docker rm $(docker ps -aq) 2>/dev/null || true

# 2. Construire l'image corrigée
print_status "Construction de l'image Docker corrigée..."
docker build -f docker/Dockerfile.prod -t sortir-app:latest .

if [ $? -ne 0 ]; then
    print_error "Erreur lors de la construction"
    exit 1
fi

print_success "Image construite avec succès"

# 3. Lancer MySQL
print_status "Lancement de MySQL..."
docker run -d --name sortir-database \
    -e MYSQL_ROOT_PASSWORD=rootpassword \
    -e MYSQL_DATABASE=sortir \
    -e MYSQL_USER=sortir_user \
    -e MYSQL_PASSWORD=sortir_password \
    -p 3306:3306 \
    mysql:8.0

if [ $? -ne 0 ]; then
    print_error "Erreur lors du lancement de MySQL"
    exit 1
fi

# 4. Attendre MySQL
print_status "Attente de MySQL (30 secondes)..."
sleep 30

# 5. Lancer l'application
print_status "Lancement de l'application..."
docker run -d --name sortir-container \
    --link sortir-database:database \
    -e DATABASE_URL="mysql://sortir_user:sortir_password@database:3306/sortir?serverVersion=8.0" \
    -e APP_ENV=prod \
    -e APP_SECRET=your_secret_key_here \
    -p 8000:8000 \
    sortir-app:latest

if [ $? -ne 0 ]; then
    print_error "Erreur lors du lancement de l'application"
    exit 1
fi

# 6. Attendre l'application
print_status "Attente de l'application (15 secondes)..."
sleep 15

# 7. Vérifier les logs
print_status "Vérification des logs..."
docker logs --tail 10 sortir-container

# 8. Test de l'application
print_status "Test de l'application..."
HTTP_CODE=$(curl -s -o /dev/null -w "%{http_code}" http://localhost:8000)
if [ "$HTTP_CODE" = "200" ] || [ "$HTTP_CODE" = "302" ]; then
    print_success "Application accessible et fonctionnelle (HTTP $HTTP_CODE)"
    echo ""
    echo -e "${GREEN}🎉${NC} Déploiement terminé avec succès!"
    echo "🌐 Application accessible sur : http://localhost:8000"
    echo "🗄️ Base de données MySQL sur : localhost:3306"
else
    print_error "Application non accessible (HTTP $HTTP_CODE)"
    echo "Logs détaillés :"
    docker logs sortir-container
fi

echo ""
