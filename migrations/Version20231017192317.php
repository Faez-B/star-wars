<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20231017192317 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE heros ADD joueur_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE heros ADD CONSTRAINT FK_1F842770A9E2D76C FOREIGN KEY (joueur_id) REFERENCES joueur (id)');
        $this->addSql('CREATE INDEX IDX_1F842770A9E2D76C ON heros (joueur_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE heros DROP FOREIGN KEY FK_1F842770A9E2D76C');
        $this->addSql('DROP INDEX IDX_1F842770A9E2D76C ON heros');
        $this->addSql('ALTER TABLE heros DROP joueur_id');
    }
}
