<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260203143835 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE club ADD logo VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE playing_user CHANGE nb_buts nb_buts INT DEFAULT 0 NOT NULL, CHANGE nb_pass_d nb_pass_d INT DEFAULT 0 NOT NULL, CHANGE nb_carton_j nb_carton_j INT DEFAULT 0 NOT NULL, CHANGE nb_carton_r nb_carton_r INT DEFAULT 0 NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE club DROP logo');
        $this->addSql('ALTER TABLE playing_user CHANGE nb_buts nb_buts INT NOT NULL, CHANGE nb_pass_d nb_pass_d INT NOT NULL, CHANGE nb_carton_j nb_carton_j INT NOT NULL, CHANGE nb_carton_r nb_carton_r INT NOT NULL');
    }
}
