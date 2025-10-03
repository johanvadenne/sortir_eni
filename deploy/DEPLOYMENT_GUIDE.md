# 🚀 Guide de Déploiement - Application Sortir

## 📋 Prérequis

- ✅ MySQL 8.0+ installé et démarré
- ✅ PHP 8.2+ avec extensions : pdo_mysql, mbstring, intl, zip, xml
- ✅ Composer installé
- ✅ Serveur web (Apache/Nginx)

## 🔧 Étapes de Déploiement

### 1. Préparation de la Base de Données

```bash
# Se connecter à MySQL en tant qu'administrateur
mysql -u root -p

# Exécuter le script de création
source deploy/create_prod_user.sql
```

### 2. Configuration de l'Environnement

```bash
# Copier le template de configuration
cp deploy/env_prod_template.txt .env.local

# Modifier les valeurs dans .env.local :
# - APP_SECRET : générer une clé secrète
# - DATABASE_URL : vérifier les identifiants MySQL
# - TRUSTED_HOSTS : configurer selon votre domaine
```

### 3. Déploiement Automatique

#### Sur Windows :
```cmd
deploy\deploy.bat
```

#### Sur Linux/Mac :
```bash
chmod +x deploy/deploy.sh
./deploy/deploy.sh
```

### 4. Configuration du Serveur Web

#### Apache :
1. Copier `deploy/apache_vhost.conf` vers votre configuration Apache
2. Modifier les chemins dans le fichier
3. Redémarrer Apache

#### Nginx :
1. Copier `deploy/nginx.conf` vers votre configuration Nginx
2. Modifier les chemins dans le fichier
3. Redémarrer Nginx

### 5. Vérification

```bash
# Tester la connexion à la base de données
php bin/console doctrine:database:create --env=prod --if-not-exists

# Vérifier les routes
php bin/console debug:router --env=prod

# Tester l'application
curl http://localhost/
```

## 🔐 Sécurité

### Variables d'Environnement Importantes

```env
# Clé secrète (générer une nouvelle clé)
APP_SECRET=your_super_secret_key_here

# Base de données (utilisateur dédié)
DATABASE_URL="mysql://sortir_user:password@127.0.0.1:3306/sortir_prod"

# Domaine autorisé
TRUSTED_HOSTS='^yourdomain\.com$'
```

### Permissions

```bash
# Permissions pour les dossiers
chmod -R 755 var/
chmod -R 755 public/
chmod -R 644 config/

# Propriétaire (si nécessaire)
chown -R www-data:www-data var/
chown -R www-data:www-data public/
```

## 🚨 Dépannage

### Erreur de Connexion MySQL
```bash
# Vérifier que MySQL est démarré
systemctl status mysql  # Linux
net start MySQL80       # Windows

# Tester la connexion
mysql -u sortir_user -p sortir_prod
```

### Erreur de Permissions
```bash
# Vérifier les permissions
ls -la var/
ls -la public/

# Corriger si nécessaire
chmod -R 755 var/
chmod -R 755 public/
```

### Erreur de Cache
```bash
# Vider le cache
php bin/console cache:clear --env=prod

# Vérifier les permissions du cache
ls -la var/cache/
```

## 📊 Monitoring

### Logs à Surveiller

- `var/log/prod.log` - Logs de l'application
- `var/log/apache_error.log` - Erreurs Apache
- `var/log/nginx_error.log` - Erreurs Nginx

### Commandes Utiles

```bash
# Vérifier l'état de l'application
php bin/console about --env=prod

# Vérifier la configuration
php bin/console debug:config --env=prod

# Vérifier les routes
php bin/console debug:router --env=prod
```

## 🔄 Mise à Jour

```bash
# Mettre à jour le code
git pull origin main

# Mettre à jour les dépendances
composer install --no-dev --optimize-autoloader

# Appliquer les migrations
php bin/console doctrine:migrations:migrate --env=prod --no-interaction

# Vider le cache
php bin/console cache:clear --env=prod
```

## 📞 Support

En cas de problème :
1. Vérifier les logs dans `var/log/`
2. Vérifier la configuration dans `.env.local`
3. Tester la connexion à la base de données
4. Vérifier les permissions des fichiers

