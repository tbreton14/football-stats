<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250829230243 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE scorer (id INT AUTO_INCREMENT NOT NULL, user_id BINARY(16) DEFAULT NULL COMMENT \'(DC2Type:uuid)\', season_id BINARY(16) DEFAULT NULL COMMENT \'(DC2Type:uuid)\', id_playing INT NOT NULL, nb_goal INT NOT NULL, INDEX IDX_705707C8A76ED395 (user_id), INDEX IDX_705707C84EC001D1 (season_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE season (id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', label VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE scorer ADD CONSTRAINT FK_705707C8A76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE scorer ADD CONSTRAINT FK_705707C84EC001D1 FOREIGN KEY (season_id) REFERENCES season (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE scorer DROP FOREIGN KEY FK_705707C8A76ED395');
        $this->addSql('ALTER TABLE scorer DROP FOREIGN KEY FK_705707C84EC001D1');
        $this->addSql('DROP TABLE scorer');
        $this->addSql('DROP TABLE season');
    }
}
