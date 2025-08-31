<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250829232244 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE category_user_user (category_user_id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', user_id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', INDEX IDX_307248B060B693EB (category_user_id), INDEX IDX_307248B0A76ED395 (user_id), PRIMARY KEY(category_user_id, user_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE category_user_user ADD CONSTRAINT FK_307248B060B693EB FOREIGN KEY (category_user_id) REFERENCES category_user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE category_user_user ADD CONSTRAINT FK_307248B0A76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE category_user DROP FOREIGN KEY FK_608AC0EA76ED395');
        $this->addSql('DROP INDEX IDX_608AC0EA76ED395 ON category_user');
        $this->addSql('ALTER TABLE category_user DROP user_id');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE category_user_user DROP FOREIGN KEY FK_307248B060B693EB');
        $this->addSql('ALTER TABLE category_user_user DROP FOREIGN KEY FK_307248B0A76ED395');
        $this->addSql('DROP TABLE category_user_user');
        $this->addSql('ALTER TABLE category_user ADD user_id BINARY(16) DEFAULT NULL COMMENT \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE category_user ADD CONSTRAINT FK_608AC0EA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_608AC0EA76ED395 ON category_user (user_id)');
    }
}
