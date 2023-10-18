<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20231018130930 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE heros CHANGE vie vie DOUBLE PRECISION NOT NULL');
        $this->addSql('ALTER TABLE vaisseau ADD joueur_id INT DEFAULT NULL, ADD vitesse INT NOT NULL');
        $this->addSql('ALTER TABLE vaisseau ADD CONSTRAINT FK_D84A1D4EA9E2D76C FOREIGN KEY (joueur_id) REFERENCES joueur (id)');
        $this->addSql('CREATE INDEX IDX_D84A1D4EA9E2D76C ON vaisseau (joueur_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE vaisseau DROP FOREIGN KEY FK_D84A1D4EA9E2D76C');
        $this->addSql('DROP INDEX IDX_D84A1D4EA9E2D76C ON vaisseau');
        $this->addSql('ALTER TABLE vaisseau DROP joueur_id, DROP vitesse');
        $this->addSql('ALTER TABLE heros CHANGE vie vie VARCHAR(255) NOT NULL');
    }
}
