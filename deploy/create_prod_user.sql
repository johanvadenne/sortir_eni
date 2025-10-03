-- Script pour créer un utilisateur MySQL dédié à la production
-- À exécuter en tant qu'administrateur MySQL (root)

-- Créer la base de données de production
CREATE DATABASE IF NOT EXISTS sortir_prod CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Créer un utilisateur dédié pour l'application
CREATE USER IF NOT EXISTS 'sortir_user'@'localhost' IDENTIFIED BY 'sortir_secure_password_2024!';

-- Donner tous les privilèges sur la base de données
GRANT ALL PRIVILEGES ON sortir_prod.* TO 'sortir_user'@'localhost';

-- Appliquer les changements
FLUSH PRIVILEGES;

-- Afficher les informations
SELECT 'Base de données et utilisateur créés avec succès' as Status;
SHOW DATABASES LIKE 'sortir_prod';
SELECT User, Host FROM mysql.user WHERE User = 'sortir_user';

