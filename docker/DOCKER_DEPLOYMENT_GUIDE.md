# 🐳 Guide de Déploiement Docker - Application Sortir

## 📋 Prérequis

- ✅ Docker Desktop installé et démarré
- ✅ Docker Compose installé
- ✅ Git (pour cloner le projet)

## 🚀 Déploiement Rapide

### **1. Cloner le projet (si nécessaire)**
```bash
git clone <votre-repo>
cd sortir
```

### **2. Déploiement automatique**

#### **Sur Windows :**
```cmd
docker\deploy.bat
```

#### **Sur Linux/Mac :**
```bash
chmod +x docker/deploy.sh
./docker/deploy.sh
```

### **3. Accéder à l'application**
- 🌐 **Application** : http://localhost:8000
- 🗄️ **Base de données** : localhost:3306

## 🔧 Configuration

### **Variables d'Environnement**

Le fichier `.env` contient la configuration :

```env
# Environnement
APP_ENV=prod
APP_SECRET=your_super_secret_key_here

# Base de données
MYSQL_ROOT_PASSWORD=rootpassword
MYSQL_DATABASE=sortir
MYSQL_USER=sortir_user
MYSQL_PASSWORD=sortir_password
```

### **Personnalisation**

1. **Copier le template** :
   ```bash
   cp docker/env_docker_template.txt .env
   ```

2. **Modifier les valeurs** dans `.env` :
   - `APP_SECRET` : Générer une clé secrète
   - `MYSQL_PASSWORD` : Mot de passe sécurisé
   - `MYSQL_ROOT_PASSWORD` : Mot de passe root MySQL

## 🎮 Commandes de Gestion

### **Démarrage/Arrêt**

```bash
# Démarrer l'application
docker-compose up -d

# Arrêter l'application
docker-compose down

# Redémarrer l'application
docker-compose restart
```

### **Logs et Debug**

```bash
# Voir les logs en temps réel
docker-compose logs -f app

# Voir les logs de la base de données
docker-compose logs -f database

# Accéder au conteneur de l'application
docker-compose exec app bash

# Accéder à MySQL
docker-compose exec database mysql -u root -p
```

### **Maintenance**

```bash
# Reconstruire les conteneurs
docker-compose up --build -d

# Vider les volumes (ATTENTION : supprime les données)
docker-compose down -v

# Nettoyer les images inutilisées
docker system prune -a
```

## 🏗️ Architecture Docker

### **Services**

1. **`database`** : MySQL 8.0
   - Port : 3306
   - Volume : `database_data`
   - Healthcheck : Vérification de la connexion

2. **`app`** : Application Symfony
   - Port : 8000
   - Build : Dockerfile personnalisé
   - Dépendance : `database` (healthcheck)

3. **`nginx`** : Serveur web (optionnel)
   - Port : 80, 443
   - Profile : `production`
   - Dépendance : `app`

### **Volumes**

- `database_data` : Données MySQL persistantes
- `./var` : Cache et logs Symfony
- `./public` : Assets publics

## 🔐 Sécurité

### **Production**

1. **Changer les mots de passe par défaut**
2. **Utiliser HTTPS** (certificats SSL)
3. **Configurer un firewall**
4. **Limiter l'accès aux ports**

### **Variables sensibles**

```env
# Générer une clé secrète
APP_SECRET=$(openssl rand -hex 32)

# Mot de passe fort pour MySQL
MYSQL_PASSWORD=$(openssl rand -base64 32)
```

## 🚨 Dépannage

### **Problèmes courants**

#### **Port déjà utilisé**
```bash
# Vérifier les ports utilisés
netstat -tulpn | grep :8000
netstat -tulpn | grep :3306

# Changer les ports dans compose.yaml
ports:
  - "8001:8000"  # Au lieu de 8000:8000
```

#### **Erreur de permissions**
```bash
# Corriger les permissions
sudo chown -R $USER:$USER .
chmod -R 755 var/
```

#### **Base de données non accessible**
```bash
# Vérifier l'état des conteneurs
docker-compose ps

# Vérifier les logs de la base de données
docker-compose logs database

# Redémarrer la base de données
docker-compose restart database
```

### **Logs utiles**

```bash
# Logs de l'application
docker-compose logs app

# Logs de la base de données
docker-compose logs database

# Logs de tous les services
docker-compose logs
```

## 📊 Monitoring

### **État des conteneurs**
```bash
docker-compose ps
```

### **Utilisation des ressources**
```bash
docker stats
```

### **Logs en temps réel**
```bash
docker-compose logs -f
```

## 🔄 Mise à Jour

### **Code de l'application**
```bash
# Mettre à jour le code
git pull origin main

# Reconstruire et redémarrer
docker-compose up --build -d
```

### **Base de données**
```bash
# Appliquer les migrations
docker-compose exec app php bin/console doctrine:migrations:migrate
```

## 📞 Support

En cas de problème :

1. **Vérifier les logs** : `docker-compose logs`
2. **Vérifier l'état** : `docker-compose ps`
3. **Redémarrer** : `docker-compose restart`
4. **Reconstruire** : `docker-compose up --build -d`

## 🎯 Avantages Docker

- ✅ **Isolation** : Environnement reproductible
- ✅ **Simplicité** : Un seul fichier de configuration
- ✅ **Portabilité** : Fonctionne sur tous les OS
- ✅ **Scalabilité** : Facile d'ajouter des services
- ✅ **Maintenance** : Mise à jour simple

