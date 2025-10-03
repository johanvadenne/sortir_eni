# 🐳 Guide d'Utilisation de l'Image Docker - Application Sortir

## 📋 Prérequis

- ✅ Docker Desktop installé et démarré
- ✅ Git (pour cloner le projet)

## 🏗️ Construction de l'Image

### **Méthode 1 : Script Automatique**

#### **Sur Windows :**
```cmd
docker\build-image.bat
```

#### **Sur Linux/Mac :**
```bash
chmod +x docker/build-image.sh
./docker/build-image.sh
```

### **Méthode 2 : Commande Manuelle**

```bash
# Construire l'image
docker build -f docker/Dockerfile -t sortir-app:latest .

# Vérifier que l'image a été créée
docker images sortir-app
```

## 🚀 Utilisation de l'Image

### **Option 1 : Application Seule (Sans Base de Données)**

#### **Script Automatique :**
```cmd
docker\run-image.bat
```

#### **Commande Manuelle :**
```bash
# Lancer l'image
docker run -d --name sortir-container -p 8000:8000 sortir-app:latest

# Voir les logs
docker logs -f sortir-container

# Accéder au conteneur
docker exec -it sortir-container bash
```

### **Option 2 : Application + Base de Données MySQL**

#### **Script Automatique :**
```cmd
docker\run-with-database.bat
```

#### **Commandes Manuelles :**

**1. Lancer MySQL :**
```bash
docker run -d --name sortir-database \
    -e MYSQL_ROOT_PASSWORD=rootpassword \
    -e MYSQL_DATABASE=sortir \
    -e MYSQL_USER=sortir_user \
    -e MYSQL_PASSWORD=sortir_password \
    -p 3306:3306 \
    mysql:8.0
```

**2. Lancer l'Application :**
```bash
docker run -d --name sortir-container \
    --link sortir-database:database \
    -e DATABASE_URL="mysql://sortir_user:sortir_password@database:3306/sortir?serverVersion=8.0" \
    -p 8000:8000 \
    sortir-app:latest
```

## 🎮 Gestion des Conteneurs

### **Commandes de Base**

```bash
# Voir les conteneurs en cours d'exécution
docker ps

# Voir tous les conteneurs (y compris arrêtés)
docker ps -a

# Arrêter un conteneur
docker stop sortir-container

# Redémarrer un conteneur
docker restart sortir-container

# Supprimer un conteneur
docker rm sortir-container

# Voir les logs
docker logs -f sortir-container
```

### **Accès aux Conteneurs**

```bash
# Accéder au conteneur de l'application
docker exec -it sortir-container bash

# Accéder à MySQL
docker exec -it sortir-database mysql -u root -p

# Exécuter des commandes Symfony
docker exec -it sortir-container php bin/console cache:clear
docker exec -it sortir-container php bin/console doctrine:migrations:migrate
```

## 🔧 Configuration

### **Variables d'Environnement**

L'image accepte les variables d'environnement suivantes :

```bash
# Base de données
DATABASE_URL=mysql://user:password@host:port/database

# Environnement Symfony
APP_ENV=prod
APP_SECRET=your_secret_key

# Configuration du serveur
TRUSTED_HOSTS='^localhost$'
```

### **Exemple de Lancement avec Configuration**

```bash
docker run -d --name sortir-container \
    -e APP_ENV=prod \
    -e APP_SECRET=your_secret_key \
    -e DATABASE_URL="mysql://user:pass@host:3306/db" \
    -p 8000:8000 \
    sortir-app:latest
```

## 📊 Monitoring et Debug

### **Logs**

```bash
# Logs en temps réel
docker logs -f sortir-container

# Logs avec timestamp
docker logs -t sortir-container

# Dernières 100 lignes
docker logs --tail 100 sortir-container
```

### **État des Conteneurs**

```bash
# État détaillé
docker inspect sortir-container

# Utilisation des ressources
docker stats sortir-container

# Processus dans le conteneur
docker top sortir-container
```

### **Debug**

```bash
# Accéder au conteneur
docker exec -it sortir-container bash

# Vérifier la configuration Symfony
docker exec -it sortir-container php bin/console about

# Vérifier les routes
docker exec -it sortir-container php bin/console debug:router

# Vérifier la base de données
docker exec -it sortir-container php bin/console doctrine:database:create --if-not-exists
```

## 🚨 Dépannage

### **Problèmes Courants**

#### **Port déjà utilisé**
```bash
# Vérifier les ports utilisés
netstat -tulpn | grep :8000

# Changer le port
docker run -p 8001:8000 sortir-app:latest
```

#### **Conteneur ne démarre pas**
```bash
# Voir les logs d'erreur
docker logs sortir-container

# Vérifier l'état
docker inspect sortir-container
```

#### **Base de données non accessible**
```bash
# Vérifier que MySQL est démarré
docker ps | grep mysql

# Tester la connexion
docker exec -it sortir-database mysql -u root -p
```

### **Nettoyage**

```bash
# Arrêter et supprimer tous les conteneurs
docker stop $(docker ps -aq)
docker rm $(docker ps -aq)

# Supprimer l'image
docker rmi sortir-app:latest

# Nettoyer les images inutilisées
docker system prune -a
```

## 🔄 Mise à Jour

### **Reconstruire l'Image**

```bash
# Reconstruire avec les dernières modifications
docker build -f docker/Dockerfile -t sortir-app:latest .

# Ou utiliser le script
docker\build-image.bat
```

### **Mise à Jour du Code**

```bash
# Arrêter le conteneur
docker stop sortir-container

# Reconstruire l'image
docker build -f docker/Dockerfile -t sortir-app:latest .

# Relancer
docker run -d --name sortir-container -p 8000:8000 sortir-app:latest
```

## 📦 Distribution de l'Image

### **Sauvegarder l'Image**

```bash
# Sauvegarder l'image
docker save -o sortir-app.tar sortir-app:latest

# Charger l'image sur un autre système
docker load -i sortir-app.tar
```

### **Publier sur Docker Hub**

```bash
# Tagger l'image
docker tag sortir-app:latest votre-username/sortir-app:latest

# Publier
docker push votre-username/sortir-app:latest
```

## 🎯 Avantages de l'Image Docker

- ✅ **Portable** : Fonctionne sur tous les systèmes
- ✅ **Isolée** : Environnement reproductible
- ✅ **Simple** : Un seul fichier à déployer
- ✅ **Rapide** : Démarrage en quelques secondes
- ✅ **Sécurisée** : Environnement isolé

## 📞 Support

En cas de problème :

1. **Vérifier les logs** : `docker logs sortir-container`
2. **Vérifier l'état** : `docker ps`
3. **Redémarrer** : `docker restart sortir-container`
4. **Reconstruire** : `docker\build-image.bat`

