<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250829231432 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE category_user ADD season_id BINARY(16) DEFAULT NULL COMMENT \'(DC2Type:uuid)\', CHANGE season season VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE category_user ADD CONSTRAINT FK_608AC0E4EC001D1 FOREIGN KEY (season_id) REFERENCES season (id)');
        $this->addSql('CREATE INDEX IDX_608AC0E4EC001D1 ON category_user (season_id)');
        $this->addSql('ALTER TABLE competition ADD season_id BINARY(16) DEFAULT NULL COMMENT \'(DC2Type:uuid)\', CHANGE season season VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE competition ADD CONSTRAINT FK_B50A2CB14EC001D1 FOREIGN KEY (season_id) REFERENCES season (id)');
        $this->addSql('CREATE INDEX IDX_B50A2CB14EC001D1 ON competition (season_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE competition DROP FOREIGN KEY FK_B50A2CB14EC001D1');
        $this->addSql('DROP INDEX IDX_B50A2CB14EC001D1 ON competition');
        $this->addSql('ALTER TABLE competition DROP season_id, CHANGE season season VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE category_user DROP FOREIGN KEY FK_608AC0E4EC001D1');
        $this->addSql('DROP INDEX IDX_608AC0E4EC001D1 ON category_user');
        $this->addSql('ALTER TABLE category_user DROP season_id, CHANGE season season VARCHAR(255) NOT NULL');
    }
}
