<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260130092916 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE playing_user ADD season_id BINARY(16) DEFAULT NULL COMMENT \'(DC2Type:uuid)\', CHANGE external_playing_id external_playing_id VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE playing_user ADD CONSTRAINT FK_703CBB214EC001D1 FOREIGN KEY (season_id) REFERENCES season (id)');
        $this->addSql('CREATE INDEX IDX_703CBB214EC001D1 ON playing_user (season_id)');

    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE playing_user DROP FOREIGN KEY FK_703CBB214EC001D1');
        $this->addSql('DROP INDEX IDX_703CBB214EC001D1 ON playing_user');
        $this->addSql('ALTER TABLE playing_user DROP season_id, CHANGE external_playing_id external_playing_id VARCHAR(255) NOT NULL');
    }
}
