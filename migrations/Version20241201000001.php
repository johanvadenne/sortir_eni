<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241201000001 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Mise à jour des états pour correspondre au workflow';
    }

    public function up(Schema $schema): void
    {
        // Mettre à jour les libellés des états pour correspondre aux places du workflow
        $this->addSql("UPDATE etat SET libelle = 'Créée' WHERE libelle = 'Créée'");
        $this->addSql("UPDATE etat SET libelle = 'Ouverte' WHERE libelle = 'Ouverte'");
        $this->addSql("UPDATE etat SET libelle = 'Clôturée' WHERE libelle = 'Clôturée'");
        $this->addSql("UPDATE etat SET libelle = 'Activité en cours' WHERE libelle = 'Activité en cours'");
        $this->addSql("UPDATE etat SET libelle = 'Activité terminée' WHERE libelle = 'Activité terminée'");
        $this->addSql("UPDATE etat SET libelle = 'Annulée' WHERE libelle = 'Annulée'");
        $this->addSql("UPDATE etat SET libelle = 'Activité historisée' WHERE libelle = 'Activité historisée'");
    }

    public function down(Schema $schema): void
    {
        // Pas de rollback nécessaire car les libellés restent les mêmes
    }
}
