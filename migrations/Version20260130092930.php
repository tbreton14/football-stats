<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260130092930 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
    

        $conn = $this->connection;

        $conn->update("playing_user", ['external_playing_id'=> NULL], ['external_playing_id' =>'']);

        $seasonId = $this->connection->fetchOne(
            'SELECT id FROM season WHERE label = :label',
            ['label' => '2025-2026']
        );

        $conn->executeStatement(
        '
        UPDATE playing_user
        SET season_id = :seasonId
        WHERE season_id IS NULL
        AND external_playing_id IS NOT NULL
        ',
        ['seasonId' => $seasonId]
        );

        $conn->executeStatement(
        '
        UPDATE playing_user plu
        LEFT JOIN playing p ON p.id = plu.playing_id
        LEFT JOIN competition c ON c.id = p.competition_id
        SET plu.season_id = c.season_id
        WHERE plu.season_id IS NULL
        AND plu.playing_id IS NOT NULL
        AND c.season_id IS NOT NULL
        '
        );

    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
    }
}
