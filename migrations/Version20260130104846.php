<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260130104846 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE category_season (id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', category_id BINARY(16) DEFAULT NULL COMMENT \'(DC2Type:uuid)\', season_id BINARY(16) DEFAULT NULL COMMENT \'(DC2Type:uuid)\', created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, deleted_at DATETIME DEFAULT NULL, INDEX IDX_FF0C91D212469DE2 (category_id), INDEX IDX_FF0C91D24EC001D1 (season_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE category_season_user (category_season_id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', user_id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', INDEX IDX_BC4108BC89A0D3E1 (category_season_id), INDEX IDX_BC4108BCA76ED395 (user_id), PRIMARY KEY(category_season_id, user_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE category_season ADD CONSTRAINT FK_FF0C91D212469DE2 FOREIGN KEY (category_id) REFERENCES category (id)');
        $this->addSql('ALTER TABLE category_season ADD CONSTRAINT FK_FF0C91D24EC001D1 FOREIGN KEY (season_id) REFERENCES season (id)');
        $this->addSql('ALTER TABLE category_season_user ADD CONSTRAINT FK_BC4108BC89A0D3E1 FOREIGN KEY (category_season_id) REFERENCES category_season (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE category_season_user ADD CONSTRAINT FK_BC4108BCA76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id) ON DELETE CASCADE');

        $this->addSql("
            INSERT INTO category_season (
                id,
                category_id,
                season_id,
                created_at,
                updated_at,
                deleted_at
            )
            SELECT
                id,
                category_id,
                season_id,
                created_at,
                updated_at,
                deleted_at
            FROM category_user
        ");


        $this->addSql("
            INSERT INTO category_season_user (
                category_season_id,
                user_id
            )
            SELECT
                category_user_id,
                user_id
            FROM category_user_user
        ");

        
        $this->addSql('ALTER TABLE category_user DROP FOREIGN KEY FK_608AC0E12469DE2');
        $this->addSql('ALTER TABLE category_user DROP FOREIGN KEY FK_608AC0E4EC001D1');
        $this->addSql('ALTER TABLE category_user_user DROP FOREIGN KEY FK_307248B060B693EB');
        $this->addSql('ALTER TABLE category_user_user DROP FOREIGN KEY FK_307248B0A76ED395');
        $this->addSql('DROP TABLE category_user');
        $this->addSql('DROP TABLE category_user_user');
        
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE category_user (id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', category_id BINARY(16) DEFAULT NULL COMMENT \'(DC2Type:uuid)\', season_id BINARY(16) DEFAULT NULL COMMENT \'(DC2Type:uuid)\', created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, deleted_at DATETIME DEFAULT NULL, INDEX IDX_608AC0E12469DE2 (category_id), INDEX IDX_608AC0E4EC001D1 (season_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE category_user_user (category_user_id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', user_id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', INDEX IDX_307248B060B693EB (category_user_id), INDEX IDX_307248B0A76ED395 (user_id), PRIMARY KEY(category_user_id, user_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE category_user ADD CONSTRAINT FK_608AC0E12469DE2 FOREIGN KEY (category_id) REFERENCES category (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE category_user ADD CONSTRAINT FK_608AC0E4EC001D1 FOREIGN KEY (season_id) REFERENCES season (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE category_user_user ADD CONSTRAINT FK_307248B060B693EB FOREIGN KEY (category_user_id) REFERENCES category_user (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE category_user_user ADD CONSTRAINT FK_307248B0A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE category_season DROP FOREIGN KEY FK_FF0C91D212469DE2');
        $this->addSql('ALTER TABLE category_season DROP FOREIGN KEY FK_FF0C91D24EC001D1');
        $this->addSql('ALTER TABLE category_season_user DROP FOREIGN KEY FK_BC4108BC89A0D3E1');
        $this->addSql('ALTER TABLE category_season_user DROP FOREIGN KEY FK_BC4108BCA76ED395');
        $this->addSql('DROP TABLE category_season');
        $this->addSql('DROP TABLE category_season_user');
    }
}
