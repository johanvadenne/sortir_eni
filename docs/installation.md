# Guide d'Installation - ENI-Sortir

## 📋 Prérequis

### Système d'exploitation
- **Windows** : Windows 10/11 ou Windows Server 2019+
- **Linux** : Ubuntu 20.04+, CentOS 8+, Debian 11+
- **macOS** : macOS 10.15+ (Catalina ou plus récent)

### Logiciels requis
- **PHP** : Version 8.2 ou supérieure
- **Composer** : Version 2.0+
- **Node.js** : Version 16+ (pour les assets)
- **Base de données** : MySQL 8.0+ ou PostgreSQL 13+
- **Serveur web** : Apache 2.4+ ou Nginx 1.18+

### Extensions PHP requises
```bash
php -m | grep -E "(ctype|iconv|pdo|pdo_mysql|json|mbstring|xml|zip|curl|gd|intl)"
```

Extensions nécessaires :
- `ctype`
- `iconv`
- `pdo`
- `pdo_mysql` ou `pdo_pgsql`
- `json`
- `mbstring`
- `xml`
- `zip`
- `curl`
- `gd`
- `intl`

## 🚀 Installation

### 1. Cloner le projet

```bash
# Via HTTPS
git clone https://github.com/votre-org/ENI-Sortir.git
cd ENI-Sortir

# Via SSH (si configuré)
git clone git@github.com:votre-org/ENI-Sortir.git
cd ENI-Sortir
```

### 2. Installer les dépendances PHP

```bash
# Installation des dépendances
composer install

# En production, optimiser l'autoloader
composer install --no-dev --optimize-autoloader
```

### 3. Configuration de l'environnement

```bash
# Copier le fichier d'environnement
cp .env .env.local

# Éditer la configuration
nano .env.local
```

**Configuration minimale dans `.env.local`** :
```env
# Environnement
APP_ENV=dev
APP_SECRET=your-secret-key-here

# Base de données
DATABASE_URL="mysql://username:password@127.0.0.1:3306/sortir"

# Mailer (optionnel)
MAILER_DSN=smtp://localhost:1025
```

### 4. Configuration de la base de données

#### MySQL
```sql
CREATE DATABASE sortir CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'sortir_user'@'localhost' IDENTIFIED BY 'secure_password';
GRANT ALL PRIVILEGES ON sortir.* TO 'sortir_user'@'localhost';
FLUSH PRIVILEGES;
```

#### PostgreSQL
```sql
CREATE DATABASE sortir;
CREATE USER sortir_user WITH PASSWORD 'secure_password';
GRANT ALL PRIVILEGES ON DATABASE sortir TO sortir_user;
```

### 5. Créer la base de données et exécuter les migrations

```bash
# Créer la base de données
php bin/console doctrine:database:create

# Exécuter les migrations
php bin/console doctrine:migrations:migrate

# Vérifier le statut des migrations
php bin/console doctrine:migrations:status
```

### 6. Charger les données de test

```bash
# Charger les fixtures
php bin/console doctrine:fixtures:load

# Ou avec confirmation
php bin/console doctrine:fixtures:load --no-interaction
```

### 7. Créer un administrateur

```bash
# Créer un compte administrateur
php bin/console app:create-admin

# Suivre les instructions à l'écran
```

### 8. Installer et compiler les assets

```bash
# Installer les dépendances Node.js
npm install

# Compiler les assets pour le développement
npm run dev

# Ou pour la production
npm run build
```

### 9. Configurer les permissions (Linux/macOS)

```bash
# Donner les permissions d'écriture
chmod -R 755 var/
chmod -R 755 public/

# Propriétaire (remplacer www-data par votre utilisateur web)
sudo chown -R www-data:www-data var/
sudo chown -R www-data:www-data public/
```

## 🌐 Configuration du serveur web

### Apache

#### Configuration VirtualHost
```apache
<VirtualHost *:80>
    ServerName sortir.local
    DocumentRoot /path/to/ENI-Sortir/public

    <Directory /path/to/ENI-Sortir/public>
        AllowOverride All
        Require all granted

        # Redirection vers index.php
        FallbackResource /index.php
    </Directory>

    # Logs
    ErrorLog ${APACHE_LOG_DIR}/sortir_error.log
    CustomLog ${APACHE_LOG_DIR}/sortir_access.log combined
</VirtualHost>
```

#### Fichier .htaccess (déjà inclus)
```apache
# public/.htaccess
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteRule ^(.*)$ index.php [QSA,L]
```

### Nginx

#### Configuration serveur
```nginx
server {
    listen 80;
    server_name sortir.local;
    root /path/to/ENI-Sortir/public;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php$is_args$args;
    }

    location ~ ^/index\.php(/|$) {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_split_path_info ^(.+\.php)(/.*)$;
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        fastcgi_param DOCUMENT_ROOT $realpath_root;
    }

    location ~ \.php$ {
        return 404;
    }

    error_log /var/log/nginx/sortir_error.log;
    access_log /var/log/nginx/sortir_access.log;
}
```

## 🔧 Configuration avancée

### Variables d'environnement complètes

```env
# .env.local
APP_ENV=prod
APP_SECRET=your-very-secure-secret-key-here
APP_DEBUG=false

# Base de données
DATABASE_URL="mysql://username:password@127.0.0.1:3306/sortir"

# Mailer
MAILER_DSN=smtp://user:pass@smtp.example.com:587

# Cache
CACHE_DSN=redis://localhost:6379

# Session
SESSION_DSN=redis://localhost:6379

# Logs
LOG_LEVEL=info
```

### Configuration PHP

#### php.ini recommandé
```ini
; Mémoire et temps d'exécution
memory_limit = 256M
max_execution_time = 300
max_input_time = 300

; Uploads
upload_max_filesize = 10M
post_max_size = 10M

; Sessions
session.gc_maxlifetime = 3600
session.cookie_lifetime = 0

; Opcache (production)
opcache.enable=1
opcache.memory_consumption=128
opcache.interned_strings_buffer=8
opcache.max_accelerated_files=4000
opcache.revalidate_freq=2
opcache.fast_shutdown=1
```

## 🧪 Vérification de l'installation

### 1. Test de base
```bash
# Vérifier la configuration Symfony
php bin/console about

# Tester la connexion à la base de données
php bin/console doctrine:database:create --if-not-exists

# Vérifier les routes
php bin/console debug:router
```

### 2. Test du serveur web
```bash
# Démarrer le serveur de développement
symfony server:start

# Ou avec PHP intégré
php -S localhost:8000 -t public/
```

### 3. Accès à l'application
- **URL** : http://localhost:8000
- **Login admin** : Utiliser les identifiants créés avec `app:create-admin`
- **Données de test** : Chargées automatiquement avec les fixtures

## 🔄 Mise à jour

### Mise à jour du code
```bash
# Récupérer les dernières modifications
git pull origin main

# Mettre à jour les dépendances
composer install

# Exécuter les nouvelles migrations
php bin/console doctrine:migrations:migrate

# Vider le cache
php bin/console cache:clear

# Recompiler les assets
npm run build
```

### Mise à jour de la base de données
```bash
# Voir les migrations en attente
php bin/console doctrine:migrations:status

# Exécuter les migrations
php bin/console doctrine:migrations:migrate

# En cas de problème, rollback
php bin/console doctrine:migrations:migrate prev
```

## 🐛 Dépannage

### Problèmes courants

#### Erreur de permissions
```bash
# Linux/macOS
sudo chown -R www-data:www-data var/
sudo chmod -R 755 var/

# Windows (IIS)
icacls var /grant "IIS_IUSRS:(OI)(CI)F"
```

#### Erreur de base de données
```bash
# Vérifier la connexion
php bin/console doctrine:database:create --if-not-exists

# Réinitialiser la base
php bin/console doctrine:database:drop --force
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
```

#### Erreur de cache
```bash
# Vider le cache
php bin/console cache:clear

# Vider le cache en production
php bin/console cache:clear --env=prod
```

#### Erreur d'assets
```bash
# Réinstaller les dépendances
rm -rf node_modules package-lock.json
npm install
npm run build
```

### Logs de débogage

#### Activer le mode debug
```env
# .env.local
APP_ENV=dev
APP_DEBUG=true
```

#### Consulter les logs
```bash
# Logs de l'application
tail -f var/log/dev.log

# Logs du serveur web
tail -f /var/log/apache2/error.log
tail -f /var/log/nginx/error.log
```

## 🚀 Déploiement en production

### Checklist de déploiement

#### Pré-déploiement
- [ ] Tests passent
- [ ] Code review effectué
- [ ] Base de données sauvegardée
- [ ] Variables d'environnement configurées

#### Déploiement
- [ ] Code déployé
- [ ] Dépendances installées
- [ ] Migrations exécutées
- [ ] Cache vidé
- [ ] Assets compilés
- [ ] Permissions configurées

#### Post-déploiement
- [ ] Tests de régression
- [ ] Monitoring activé
- [ ] Logs vérifiés
- [ ] Performance testée

### Script de déploiement

```bash
#!/bin/bash
# deploy.sh

set -e

echo "🚀 Déploiement ENI-Sortir"

# Sauvegarde
echo "📦 Sauvegarde de la base de données"
mysqldump -u username -p sortir > backup_$(date +%Y%m%d_%H%M%S).sql

# Déploiement du code
echo "📥 Mise à jour du code"
git pull origin main

# Dépendances
echo "📦 Installation des dépendances"
composer install --no-dev --optimize-autoloader

# Base de données
echo "🗄️ Mise à jour de la base de données"
php bin/console doctrine:migrations:migrate --no-interaction

# Cache
echo "🧹 Nettoyage du cache"
php bin/console cache:clear --env=prod

# Assets
echo "🎨 Compilation des assets"
npm run build

# Permissions
echo "🔐 Configuration des permissions"
chmod -R 755 var/
chown -R www-data:www-data var/

echo "✅ Déploiement terminé"
```

## 📊 Monitoring

### Configuration du monitoring

#### Health check
```bash
# Endpoint de santé
curl http://localhost:8000/health

# Vérification de la base de données
php bin/console doctrine:database:create --if-not-exists
```

#### Logs de monitoring
```bash
# Surveiller les logs en temps réel
tail -f var/log/prod.log

# Analyser les erreurs
grep "ERROR" var/log/prod.log | tail -20
```

### Tâches cron recommandées

```bash
# Crontab
# Traitement des transitions automatiques (toutes les heures)
0 * * * * cd /path/to/ENI-Sortir && php bin/console app:sortie:tick

# Nettoyage des logs (quotidien)
0 2 * * * find /path/to/ENI-Sortir/var/log -name "*.log" -mtime +30 -delete

# Sauvegarde de la base de données (quotidien)
0 3 * * * mysqldump -u username -p sortir > /backups/sortir_$(date +\%Y\%m\%d).sql
```

## 🔒 Sécurité

### Configuration de sécurité

#### HTTPS
```apache
# Apache - Redirection HTTPS
<VirtualHost *:80>
    ServerName sortir.example.com
    Redirect permanent / https://sortir.example.com/
</VirtualHost>

<VirtualHost *:443>
    ServerName sortir.example.com
    DocumentRoot /path/to/ENI-Sortir/public

    SSLEngine on
    SSLCertificateFile /path/to/certificate.crt
    SSLCertificateKeyFile /path/to/private.key
</VirtualHost>
```

#### Firewall
```bash
# UFW (Ubuntu)
sudo ufw allow 22/tcp
sudo ufw allow 80/tcp
sudo ufw allow 443/tcp
sudo ufw enable
```

### Bonnes pratiques
- Changer le secret par défaut
- Utiliser HTTPS en production
- Configurer un firewall
- Mettre à jour régulièrement
- Surveiller les logs
- Sauvegarder régulièrement

---

*Ce guide d'installation est maintenu à jour avec l'évolution du projet. Pour toute question ou problème, consultez la documentation ou contactez l'équipe de développement.*

