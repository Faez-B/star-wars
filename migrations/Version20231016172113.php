<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20231016172113 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE guilde (id INT AUTO_INCREMENT NOT NULL, uniq_id VARCHAR(255) NOT NULL, nom VARCHAR(255) NOT NULL, puis_galactique INT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE joueur ADD guilde_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE joueur ADD CONSTRAINT FK_FD71A9C5A2E96BBE FOREIGN KEY (guilde_id) REFERENCES guilde (id)');
        $this->addSql('CREATE INDEX IDX_FD71A9C5A2E96BBE ON joueur (guilde_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE joueur DROP FOREIGN KEY FK_FD71A9C5A2E96BBE');
        $this->addSql('DROP TABLE guilde');
        $this->addSql('DROP INDEX IDX_FD71A9C5A2E96BBE ON joueur');
        $this->addSql('ALTER TABLE joueur DROP guilde_id');
    }
}
