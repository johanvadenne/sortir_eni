<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251001073932 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Ajout de la fonctionnalité de gestion des groupes privés pour les sorties';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE groupe (id INT AUTO_INCREMENT NOT NULL, createur_id INT NOT NULL, nom VARCHAR(50) NOT NULL, description LONGTEXT DEFAULT NULL, actif TINYINT(1) NOT NULL, date_creation DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', UNIQUE INDEX UNIQ_4B98C216C6E55B5 (nom), INDEX IDX_4B98C2173A201E5 (createur_id), INDEX idx_groupe_nom (nom), INDEX idx_groupe_actif (actif), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE groupe_participants (groupe_id INT NOT NULL, participant_id INT NOT NULL, INDEX IDX_D3A42567A45358C (groupe_id), INDEX IDX_D3A42569D1C3019 (participant_id), PRIMARY KEY(groupe_id, participant_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE groupe ADD CONSTRAINT FK_4B98C2173A201E5 FOREIGN KEY (createur_id) REFERENCES participant (id)');
        $this->addSql('ALTER TABLE groupe_participants ADD CONSTRAINT FK_D3A42567A45358C FOREIGN KEY (groupe_id) REFERENCES groupe (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE groupe_participants ADD CONSTRAINT FK_D3A42569D1C3019 FOREIGN KEY (participant_id) REFERENCES participant (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE sortie ADD groupe_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE sortie ADD CONSTRAINT FK_3C3FD3F27A45358C FOREIGN KEY (groupe_id) REFERENCES groupe (id)');
        $this->addSql('CREATE INDEX IDX_3C3FD3F27A45358C ON sortie (groupe_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE sortie DROP FOREIGN KEY FK_3C3FD3F27A45358C');
        $this->addSql('ALTER TABLE groupe DROP FOREIGN KEY FK_4B98C2173A201E5');
        $this->addSql('ALTER TABLE groupe_participants DROP FOREIGN KEY FK_D3A42567A45358C');
        $this->addSql('ALTER TABLE groupe_participants DROP FOREIGN KEY FK_D3A42569D1C3019');
        $this->addSql('DROP TABLE groupe');
        $this->addSql('DROP TABLE groupe_participants');
        $this->addSql('DROP INDEX IDX_3C3FD3F27A45358C ON sortie');
        $this->addSql('ALTER TABLE sortie DROP groupe_id');
    }
}
