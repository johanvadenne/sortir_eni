# 🚀 Guide de Déploiement Docker - Application Sortir

## 📋 Prérequis Installés

- ✅ **Docker Desktop** : Version 28.4.0 installée
- ✅ **Application Symfony** : Code source prêt
- ✅ **Scripts de déploiement** : Créés et testés

## 🎯 Déploiement en 3 Étapes

### **Étape 1 : Test Rapide**
```cmd
test-docker.bat
```
**Objectif** : Vérifier que Docker fonctionne et que l'image se construit correctement.

### **Étape 2 : Déploiement Complet**
```cmd
deploy-docker.bat
```
**Objectif** : Déployer l'application complète avec base de données MySQL.

### **Étape 3 : Gestion**
```cmd
manage-docker.bat
```
**Objectif** : Gérer l'application (démarrer, arrêter, voir les logs, etc.).

## 🚀 Déploiement Immédiat

### **Option A : Déploiement Automatique (Recommandé)**

1. **Ouvrez un terminal** dans le dossier du projet
2. **Exécutez** : `deploy-docker.bat`
3. **Attendez** que le script termine (2-3 minutes)
4. **Ouvrez** http://localhost:8000 dans votre navigateur

### **Option B : Déploiement Manuel**

```cmd
# 1. Construire l'image
docker build -f docker/Dockerfile -t sortir-app:latest .

# 2. Lancer MySQL
docker run -d --name sortir-database \
    -e MYSQL_ROOT_PASSWORD=rootpassword \
    -e MYSQL_DATABASE=sortir \
    -e MYSQL_USER=sortir_user \
    -e MYSQL_PASSWORD=sortir_password \
    -p 3306:3306 \
    mysql:8.0

# 3. Attendre MySQL (30 secondes)
timeout /t 30

# 4. Lancer l'application
docker run -d --name sortir-container \
    --link sortir-database:database \
    -e DATABASE_URL="mysql://sortir_user:sortir_password@database:3306/sortir?serverVersion=8.0" \
    -p 8000:8000 \
    sortir-app:latest
```

## 🎮 Gestion de l'Application

### **Script de Gestion Interactif**
```cmd
manage-docker.bat
```

**Fonctionnalités disponibles :**
- 🚀 Démarrer l'application
- 🛑 Arrêter l'application
- 🔄 Redémarrer l'application
- 📊 Voir l'état des conteneurs
- 📝 Voir les logs
- 🔧 Accéder aux conteneurs
- 🧹 Nettoyer tout

### **Commandes Docker Directes**

```cmd
# Voir l'état
docker ps

# Voir les logs
docker logs -f sortir-container

# Arrêter
docker stop sortir-container sortir-database

# Redémarrer
docker start sortir-container sortir-database

# Accéder à l'application
docker exec -it sortir-container bash

# Accéder à MySQL
docker exec -it sortir-database mysql -u root -p
```

## 🌐 Accès à l'Application

### **URLs d'Accès**
- **Application** : http://localhost:8000
- **Base de données** : localhost:3306

### **Identifiants de Connexion**
- **Utilisateur MySQL** : `sortir_user`
- **Mot de passe MySQL** : `sortir_password`
- **Base de données** : `sortir`
- **Root MySQL** : `rootpassword`

## 🔧 Configuration

### **Variables d'Environnement**
L'application utilise ces variables par défaut :
- `DATABASE_URL` : Connexion MySQL automatique
- `APP_ENV` : `prod` (production)
- `APP_SECRET` : Clé secrète générée

### **Ports Utilisés**
- **8000** : Application Symfony
- **3306** : Base de données MySQL

## 📊 Monitoring

### **Vérification de l'État**
```cmd
# État des conteneurs
docker ps --filter name=sortir

# Utilisation des ressources
docker stats sortir-container sortir-database

# Logs en temps réel
docker logs -f sortir-container
```

### **Tests de Fonctionnement**
```cmd
# Test de l'application
curl http://localhost:8000

# Test de la base de données
docker exec -it sortir-database mysql -u sortir_user -psortir_password -e "SHOW DATABASES;"
```

## 🚨 Dépannage

### **Problèmes Courants**

#### **Docker Desktop non démarré**
```cmd
# Démarrer Docker Desktop
start "" "C:\Program Files\Docker\Docker\Docker Desktop.exe"
# Attendre 30 secondes puis relancer le script
```

#### **Port 8000 déjà utilisé**
```cmd
# Vérifier les ports utilisés
netstat -ano | findstr :8000
# Arrêter le processus ou changer le port
```

#### **Application non accessible**
```cmd
# Vérifier les logs
docker logs sortir-container
# Redémarrer l'application
docker restart sortir-container
```

#### **Base de données non accessible**
```cmd
# Vérifier MySQL
docker logs sortir-database
# Redémarrer MySQL
docker restart sortir-database
```

### **Nettoyage Complet**
```cmd
# Arrêter et supprimer tout
docker stop sortir-container sortir-database
docker rm sortir-container sortir-database
docker rmi sortir-app:latest

# Ou utiliser le script
manage-docker.bat
# Choisir option 9 (Nettoyage)
```

## 📦 Sauvegarde et Restauration

### **Sauvegarder l'Image**
```cmd
# Sauvegarder l'image
docker save -o sortir-app-backup.tar sortir-app:latest

# Restaurer l'image
docker load -i sortir-app-backup.tar
```

### **Sauvegarder la Base de Données**
```cmd
# Sauvegarder
docker exec sortir-database mysqldump -u root -prootpassword sortir > backup.sql

# Restaurer
docker exec -i sortir-database mysql -u root -prootpassword sortir < backup.sql
```

## 🎯 Prochaines Étapes

### **Déploiement en Production**
1. **Changer les mots de passe** par défaut
2. **Configurer HTTPS** avec un reverse proxy
3. **Sauvegarder régulièrement** la base de données
4. **Monitorer** les performances

### **Améliorations Possibles**
- **Load Balancer** pour la haute disponibilité
- **Base de données** externe (RDS, etc.)
- **CDN** pour les assets statiques
- **Monitoring** avec Prometheus/Grafana

## 🎉 Félicitations !

Votre application Sortir est maintenant prête pour le déploiement Docker !

**Pour commencer immédiatement :**
```cmd
deploy-docker.bat
```

**Puis ouvrez :** http://localhost:8000

---

*Scripts créés par l'assistant IA - Prêts pour le déploiement !* 🚀
