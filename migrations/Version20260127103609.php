<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260127103609 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE category_user DROP season');
        $this->addSql('ALTER TABLE competition DROP season, DROP see_scorers_ranking, DROP see_passers_ranking');
        $this->addSql('ALTER TABLE playing_user ADD external_playing_id VARCHAR(255) NOT NULL, ADD sp INT NOT NULL');
        $this->addSql('ALTER TABLE user CHANGE roles roles JSON NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE category_user ADD season VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE competition ADD season VARCHAR(255) DEFAULT NULL, ADD see_scorers_ranking TINYINT(1) DEFAULT 1 NOT NULL, ADD see_passers_ranking TINYINT(1) DEFAULT 1 NOT NULL');
        $this->addSql('ALTER TABLE playing_user DROP external_playing_id, DROP sp');
        $this->addSql('ALTER TABLE `user` CHANGE roles roles JSON NOT NULL COLLATE `utf8mb4_bin`');
    }
}
