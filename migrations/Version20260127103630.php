<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use App\Entity\Summon;
use App\Entity\PlayingUser;
use App\Entity\Scorer;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\ORMSetup;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use  Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Uid\Uuid;

final class Version20260127103630 extends AbstractMigration
{

    public function getDescription(): string
    {
        return 'Create Planning entities from Summons and Scores';
    }

    public function up(Schema $schema): void
    {

        $conn = $this->connection;

        // 1. Récupération de tous les summons avec leurs users
        $rows = $conn->fetchAllAssociative("
            SELECT 
                s.id            AS summon_id,
                s.id_playing    AS id_playing,
                su.user_id      AS user_id
            FROM summon s
            INNER JOIN summon_user su ON su.summon_id = s.id
        ");

        foreach ($rows as $row) {
            // 2. Agrégation des scorers
            $stats = $conn->fetchAssociative("
                SELECT 
                    COALESCE(SUM(nb_goal), 0) AS nb_buts,
                    COALESCE(SUM(CASE WHEN sp = 1 THEN 1 ELSE 0 END), 0) AS sp
                FROM scorer
                WHERE user_id = :user_id
                AND id_playing = :id_playing
            ", [
                'user_id'    => $row['user_id'],
                'id_playing' => $row['id_playing'],
            ]);

            // 3. Insertion PlayingUser
        $conn->insert('playing_user', [
        'id'                   => Uuid::v4()->toBinary(),
        'user_id'              => $row['user_id'],
        'external_playing_id'  => $row['id_playing'],
        'nb_buts'              => (int) $stats['nb_buts'],
        'nb_pass_d'            => 0,
        'nb_carton_j'          => 0,
        'nb_carton_r'          => 0,
        'sp'                   => (int) $stats['sp'],
        'created_at'           => (new \DateTimeImmutable())->format('Y-m-d H:i:s'),
        'updated_at'           => (new \DateTimeImmutable())->format('Y-m-d H:i:s'),
    ]);
        }

        /*
        $em = $this->getEntityManager();


        $repoSummon = new EntityRepository(
            $em,
            $em->getClassMetadata(Summon::class)
        );

        $repoScorer = new EntityRepository(
            $em,
            $em->getClassMetadata(Scorer::class)
        );

        $summons = $repoSummon->findAll();

        foreach ($summons as $summon) {
            foreach ($summon->getUsers() as $user) {

                $playinguser = new PlayingUser();
                $playinguser->setUser($user);
                $playinguser->setExternalPlayingId($summon->getIdPlaying());

                $scorers = $repoScorer->findBy([
                    'user' => $user,
                    'idPlaying' => $playinguser->getExternalPlayingId(),
                ]);

                $nbuts=0;
                $sp=0;
                foreach ($scorers as $scorer) {
                    $nbuts = $nbuts+$scorer->getNbGoal();
                    if($scorer->getSp()) {
                        $sp = $sp++;
                    }
                }

                $playinguser->setNbButs($nbuts);
                $playinguser->setSp($sp);

                $em->persist($playinguser);
            }
        }

        $em->flush();
        */
    }

    public function down(Schema $schema): void
    {
        // Optional: delete created plannings if rollback is needed
    }
}
