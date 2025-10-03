#!/bin/bash

echo "🚀 Déploiement Docker de l'application Sortir sur Linux"
echo "========================================================"

# Couleurs pour les messages
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Fonction pour afficher les messages colorés
print_status() {
    echo -e "${BLUE}📋${NC} $1"
}

print_success() {
    echo -e "${GREEN}✅${NC} $1"
}

print_error() {
    echo -e "${RED}❌${NC} $1"
}

print_warning() {
    echo -e "${YELLOW}⚠️${NC} $1"
}

# 1. Vérifier que Docker est installé
print_status "Vérification de Docker..."
if ! command -v docker &> /dev/null; then
    print_error "Docker n'est pas installé"
    echo "Installation de Docker..."

    # Détecter la distribution
    if [ -f /etc/debian_version ]; then
        # Ubuntu/Debian
        sudo apt-get update
        sudo apt-get install -y docker.io docker-compose
        sudo systemctl start docker
        sudo systemctl enable docker
        sudo usermod -aG docker $USER
        print_warning "Docker installé. Vous devez vous déconnecter/reconnecter pour que les permissions prennent effet."
        exit 1
    elif [ -f /etc/redhat-release ]; then
        # CentOS/RHEL/Fedora
        sudo yum install -y docker docker-compose
        sudo systemctl start docker
        sudo systemctl enable docker
        sudo usermod -aG docker $USER
        print_warning "Docker installé. Vous devez vous déconnecter/reconnecter pour que les permissions prennent effet."
        exit 1
    else
        print_error "Distribution non supportée. Installez Docker manuellement."
        exit 1
    fi
fi

# Vérifier que Docker est démarré
if ! docker info &> /dev/null; then
    print_error "Docker n'est pas démarré"
    echo "Démarrage de Docker..."
    sudo systemctl start docker
    sleep 5
fi

print_success "Docker est prêt"

# 2. Nettoyer les anciens conteneurs et images
print_status "Nettoyage des anciens conteneurs..."
docker stop sortir-container sortir-database 2>/dev/null || true
docker rm sortir-container sortir-database 2>/dev/null || true
docker rmi sortir-app:latest 2>/dev/null || true

# 3. Construire l'image
print_status "Construction de l'image Docker..."
docker build -f docker/Dockerfile.prod -t sortir-app:latest .

if [ $? -ne 0 ]; then
    print_error "Erreur lors de la construction de l'image"
    echo "Vérifiez que tous les fichiers sont présents"
    exit 1
fi
print_success "Image construite avec succès"

# 4. Lancer la base de données MySQL
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
print_success "MySQL démarré"

# 5. Attendre que MySQL soit prêt
print_status "Attente du démarrage de MySQL (30 secondes)..."
sleep 30

# 6. Lancer l'application
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
print_success "Application démarrée"

# 7. Attendre que l'application soit prête
print_status "Attente du démarrage de l'application (15 secondes)..."
sleep 15

# 8. Vérifier l'état
print_status "État des conteneurs..."
docker ps --filter name=sortir

# 9. Afficher les informations de déploiement
echo ""
echo -e "${GREEN}🎉 DÉPLOIEMENT TERMINÉ AVEC SUCCÈS!${NC}"
echo "========================================================"
echo ""
echo -e "${BLUE}🌐${NC} Application accessible sur : http://localhost:8000"
echo -e "${BLUE}🗄️${NC} Base de données MySQL sur : localhost:3306"
echo ""
echo -e "${BLUE}📋${NC} Informations de connexion :"
echo "   - Utilisateur MySQL : sortir_user"
echo "   - Mot de passe MySQL : sortir_password"
echo "   - Base de données : sortir"
echo ""
echo -e "${BLUE}📋${NC} Commandes utiles :"
echo "   - docker logs -f sortir-container  : Voir les logs de l'app"
echo "   - docker logs -f sortir-database   : Voir les logs de MySQL"
echo "   - docker exec -it sortir-container bash  : Accéder à l'app"
echo "   - docker stop sortir-container sortir-database  : Arrêter l'application"
echo "   - docker start sortir-container sortir-database : Redémarrer l'application"
echo ""
echo -e "${BLUE}🔧${NC} Pour arrêter l'application : docker stop sortir-container sortir-database"
echo -e "${BLUE}🔄${NC} Pour redémarrer l'application : docker start sortir-container sortir-database"
echo ""

# 10. Test de l'application
print_status "Test de l'application..."
if curl -s -o /dev/null -w "%{http_code}" http://localhost:8000 | grep -q "200\|302"; then
    print_success "Application accessible et fonctionnelle"
else
    print_warning "Application en cours de démarrage, veuillez patienter..."
fi

echo ""
echo -e "${GREEN}🎯${NC} Votre application Sortir est maintenant déployée et accessible !"
echo "   Ouvrez votre navigateur et allez sur http://localhost:8000"
echo ""
