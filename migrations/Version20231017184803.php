<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20231017184803 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE heros (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(255) NOT NULL, base_id VARCHAR(255) NOT NULL, vie VARCHAR(255) NOT NULL, protection DOUBLE PRECISION NOT NULL, puissance INT NOT NULL, tenacite DOUBLE PRECISION NOT NULL, degats_physiques DOUBLE PRECISION NOT NULL, degat_speciaux DOUBLE PRECISION NOT NULL, chance_ccdegats_phys DOUBLE PRECISION NOT NULL, chance_ccdegats_spe DOUBLE PRECISION NOT NULL, degat_critique INT NOT NULL, vol_vie INT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE heros');
    }
}
