#!/bin/bash

echo "🔍 Vérification de la connexion à la base de données"
echo "==================================================="

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

# 1. Vérifier si MySQL est en cours d'exécution
print_status "Vérification de MySQL..."
if docker ps | grep -q "mysql"; then
    print_success "MySQL est en cours d'exécution"
else
    print_error "MySQL n'est pas en cours d'exécution"
    echo "Lancement de MySQL..."
    docker run -d --name sortir-database \
        -e MYSQL_ROOT_PASSWORD=rootpassword \
        -e MYSQL_DATABASE=sortir \
        -e MYSQL_USER=sortir_user \
        -e MYSQL_PASSWORD=sortir_password \
        -p 3306:3306 \
        mysql:8.0
    echo "Attente de MySQL (30 secondes)..."
    sleep 30
fi

# 2. Tester la connexion MySQL
print_status "Test de connexion MySQL..."
if docker exec sortir-database mysql -u root -prootpassword -e "SELECT 1;" >/dev/null 2>&1; then
    print_success "Connexion MySQL réussie"
else
    print_error "Connexion MySQL échouée"
    echo "Logs MySQL :"
    docker logs --tail 10 sortir-database
fi

# 3. Vérifier si la base de données existe
print_status "Vérification de la base de données 'sortir'..."
if docker exec sortir-database mysql -u root -prootpassword -e "USE sortir; SELECT 1;" >/dev/null 2>&1; then
    print_success "Base de données 'sortir' existe"
else
    print_error "Base de données 'sortir' n'existe pas"
    echo "Création de la base de données..."
    docker exec sortir-database mysql -u root -prootpassword -e "CREATE DATABASE IF NOT EXISTS sortir;"
fi

# 4. Vérifier l'utilisateur
print_status "Vérification de l'utilisateur 'sortir_user'..."
if docker exec sortir-database mysql -u sortir_user -psortir_password -e "SELECT 1;" >/dev/null 2>&1; then
    print_success "Utilisateur 'sortir_user' fonctionne"
else
    print_error "Utilisateur 'sortir_user' ne fonctionne pas"
    echo "Création de l'utilisateur..."
    docker exec sortir-database mysql -u root -prootpassword -e "CREATE USER IF NOT EXISTS 'sortir_user'@'%' IDENTIFIED BY 'sortir_password'; GRANT ALL PRIVILEGES ON sortir.* TO 'sortir_user'@'%'; FLUSH PRIVILEGES;"
fi

# 5. Tester la connexion depuis l'application
print_status "Test de connexion depuis l'application..."
if docker exec sortir-container php bin/console doctrine:database:create --if-not-exists >/dev/null 2>&1; then
    print_success "Connexion depuis l'application réussie"
else
    print_error "Connexion depuis l'application échouée"
    echo "Vérification de la configuration DATABASE_URL..."
    docker exec sortir-container env | grep DATABASE_URL
fi

echo ""
echo "📋 Informations de connexion :"
echo "   - Host: localhost:3306"
echo "   - Database: sortir"
echo "   - User: sortir_user"
echo "   - Password: sortir_password"
echo "   - Root Password: rootpassword"
echo ""
