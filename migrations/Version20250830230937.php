<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250830230937 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE summon (id INT AUTO_INCREMENT NOT NULL, season_id BINARY(16) DEFAULT NULL COMMENT \'(DC2Type:uuid)\', id_playing INT NOT NULL, INDEX IDX_CC6AFDE94EC001D1 (season_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE summon_user (summon_id INT NOT NULL, user_id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', INDEX IDX_A3711B32CC073965 (summon_id), INDEX IDX_A3711B32A76ED395 (user_id), PRIMARY KEY(summon_id, user_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE summon ADD CONSTRAINT FK_CC6AFDE94EC001D1 FOREIGN KEY (season_id) REFERENCES season (id)');
        $this->addSql('ALTER TABLE summon_user ADD CONSTRAINT FK_A3711B32CC073965 FOREIGN KEY (summon_id) REFERENCES summon (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE summon_user ADD CONSTRAINT FK_A3711B32A76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE summon DROP FOREIGN KEY FK_CC6AFDE94EC001D1');
        $this->addSql('ALTER TABLE summon_user DROP FOREIGN KEY FK_A3711B32CC073965');
        $this->addSql('ALTER TABLE summon_user DROP FOREIGN KEY FK_A3711B32A76ED395');
        $this->addSql('DROP TABLE summon');
        $this->addSql('DROP TABLE summon_user');
    }
}
