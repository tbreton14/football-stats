<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260918141631 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE photo (id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', season_id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', competition_id BINARY(16) DEFAULT NULL COMMENT \'(DC2Type:uuid)\', category_id BINARY(16) DEFAULT NULL COMMENT \'(DC2Type:uuid)\', title VARCHAR(255) DEFAULT NULL, file_name VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, INDEX IDX_14B784184EC001D1 (season_id), INDEX IDX_14B784187B39D312 (competition_id), INDEX IDX_14B7841812469DE2 (category_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE photo ADD CONSTRAINT FK_14B784184EC001D1 FOREIGN KEY (season_id) REFERENCES season (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE photo ADD CONSTRAINT FK_14B784187B39D312 FOREIGN KEY (competition_id) REFERENCES competition (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE photo ADD CONSTRAINT FK_14B7841812469DE2 FOREIGN KEY (category_id) REFERENCES category (id) ON DELETE SET NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE photo DROP FOREIGN KEY FK_14B784184EC001D1');
        $this->addSql('ALTER TABLE photo DROP FOREIGN KEY FK_14B784187B39D312');
        $this->addSql('ALTER TABLE photo DROP FOREIGN KEY FK_14B7841812469DE2');
        $this->addSql('DROP TABLE photo');
    }
}
