# 🐧 Guide de Déploiement Linux - Application Sortir

## 🚀 Déploiement Rapide (3 Commandes)

### **1. Construire l'Image**
```bash
chmod +x build-linux.sh
./build-linux.sh
```

### **2. Tester l'Image**
```bash
chmod +x test-linux.sh
./test-linux.sh
```

### **3. Déployer Complètement**
```bash
chmod +x deploy-linux.sh
./deploy-linux.sh
```

## 📋 Prérequis

- ✅ **Docker** installé et démarré
- ✅ **Git** pour cloner le projet
- ✅ **Permissions** pour exécuter Docker

### **Installation de Docker (si nécessaire)**
```bash
# Ubuntu/Debian
sudo apt-get update
sudo apt-get install docker.io docker-compose
sudo systemctl start docker
sudo systemctl enable docker
sudo usermod -aG docker $USER

# RedHat/CentOS/Fedora
sudo yum install docker docker-compose
sudo systemctl start docker
sudo systemctl enable docker
sudo usermod -aG docker $USER
```

## 🎯 Déploiement avec Docker Compose (Recommandé)

### **Option 1 : Docker Compose Simple**
```bash
# Lancer avec Docker Compose
docker-compose up -d

# Voir les logs
docker-compose logs -f

# Arrêter
docker-compose down
```

### **Option 2 : Docker Compose avec Build**
```bash
# Construire et lancer
docker-compose up -d --build

# Redémarrer après modification
docker-compose restart
```

## 🔧 Déploiement Manuel

### **1. Construire l'Image**
```bash
docker build -f docker/Dockerfile.prod -t sortir-app:latest .
```

### **2. Lancer MySQL**
```bash
docker run -d --name sortir-database \
    -e MYSQL_ROOT_PASSWORD=rootpassword \
    -e MYSQL_DATABASE=sortir \
    -e MYSQL_USER=sortir_user \
    -e MYSQL_PASSWORD=sortir_password \
    -p 3306:3306 \
    mysql:8.0
```

### **3. Lancer l'Application**
```bash
docker run -d --name sortir-container \
    --link sortir-database:database \
    -e DATABASE_URL="mysql://sortir_user:sortir_password@database:3306/sortir?serverVersion=8.0" \
    -e APP_ENV=prod \
    -p 8000:8000 \
    sortir-app:latest
```

## 🎮 Gestion de l'Application

### **Commandes Utiles**
```bash
# Voir l'état des conteneurs
docker ps

# Voir les logs
docker logs -f sortir-container
docker logs -f sortir-database

# Arrêter l'application
docker stop sortir-container sortir-database

# Redémarrer l'application
docker start sortir-container sortir-database

# Accéder au conteneur
docker exec -it sortir-container bash

# Accéder à MySQL
docker exec -it sortir-database mysql -u root -p
```

### **Nettoyage**
```bash
# Arrêter et supprimer les conteneurs
docker stop sortir-container sortir-database
docker rm sortir-container sortir-database

# Supprimer l'image
docker rmi sortir-app:latest

# Nettoyer tout
docker system prune -a
```

## 🌐 Accès à l'Application

- **Application** : http://localhost:8000
- **Base de données** : localhost:3306

### **Identifiants**
- **Utilisateur MySQL** : `sortir_user`
- **Mot de passe MySQL** : `sortir_password`
- **Base de données** : `sortir`
- **Root MySQL** : `rootpassword`

## 🚨 Dépannage

### **Problèmes Courants**

#### **Permission Denied**
```bash
# Ajouter l'utilisateur au groupe docker
sudo usermod -aG docker $USER
# Se déconnecter/reconnecter
```

#### **Port déjà utilisé**
```bash
# Vérifier les ports utilisés
sudo netstat -tulpn | grep :8000
# Arrêter le processus ou changer le port
```

#### **Image ne se construit pas**
```bash
# Vérifier les logs de build
docker build -f docker/Dockerfile.prod -t sortir-app:latest . --no-cache

# Vérifier l'espace disque
df -h
```

#### **Application non accessible**
```bash
# Vérifier les logs
docker logs sortir-container

# Vérifier l'état des conteneurs
docker ps -a

# Redémarrer
docker restart sortir-container
```

## 📊 Monitoring

### **État des Conteneurs**
```bash
# État détaillé
docker ps --format "table {{.Names}}\t{{.Status}}\t{{.Ports}}"

# Utilisation des ressources
docker stats --no-stream
```

### **Logs en Temps Réel**
```bash
# Logs de l'application
docker logs -f sortir-container

# Logs de la base de données
docker logs -f sortir-database

# Logs avec timestamp
docker logs -t sortir-container
```

## 🔄 Mise à Jour

### **Reconstruire l'Image**
```bash
# Reconstruire avec les dernières modifications
docker build -f docker/Dockerfile.prod -t sortir-app:latest . --no-cache

# Ou utiliser le script
./build-linux.sh
```

### **Mise à Jour du Code**
```bash
# Arrêter l'application
docker stop sortir-container

# Reconstruire l'image
docker build -f docker/Dockerfile.prod -t sortir-app:latest .

# Relancer
docker start sortir-container
```

## 🎯 Avantages Linux

- ✅ **Performance** : Meilleure performance que Windows
- ✅ **Stabilité** : Plus stable pour les serveurs
- ✅ **Ressources** : Moins de consommation de ressources
- ✅ **Sécurité** : Meilleure sécurité native
- ✅ **Outils** : Accès à tous les outils Linux

## 🎉 Félicitations !

Votre application Sortir est maintenant prête pour le déploiement Linux !

**Pour commencer immédiatement :**
```bash
./build-linux.sh && ./deploy-linux.sh
```

**Puis ouvrez :** http://localhost:8000

---

*Scripts optimisés pour Linux - Prêts pour le déploiement !* 🚀
