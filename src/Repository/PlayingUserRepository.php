<?php

namespace App\Repository;

use App\Entity\PlayingUser;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\ParameterType;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bridge\Doctrine\Types\UuidType;

/**
 * @extends ServiceEntityRepository<PlayingUser>
 *
 * @method PlayingUser|null find($id, $lockMode = null, $lockVersion = null)
 * @method PlayingUser|null findOneBy(array $criteria, array $orderBy = null)
 * @method PlayingUser[]    findAll()
 * @method PlayingUser[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PlayingUserRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PlayingUser::class);
    }

    /**
     * @return PlayingUser[] Returns an array of PlayingUser objects
     */
    public function findByCompetition($value): array
    {
        return $this->createQueryBuilder('p')
            ->leftJoin('p.playing', 'pl')
            ->andWhere('pl.competition = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getResult()
        ;
    }

    public function findPlayingsUserBySeason($user, $season) {
        return $this->createQueryBuilder('plu')
            ->andWhere('plu.user = :user')
            ->setParameter('user', $user)
            ->andWhere('plu.season = :season')
            ->setParameter('season', $season)
            ->getQuery()
            ->getResult()
            ;
    }

    public function findByPlaying($playing) {
        return $this->createQueryBuilder('plu')
            ->select('u.firstName, u.lastName, plu.nbButs, plu.nbPassD, plu.sp, plu.nbCartonJ, plu.nbCartonR')
            ->leftJoin('plu.playing', 'p')
            ->leftJoin('plu.user', 'u')
            ->leftJoin('u.userPoste', 'po')
            ->andWhere('plu.playing = :playing')
            ->setParameter('playing', $playing->getId()->toBinary(), ParameterType::BINARY)
            ->orderBy('po.zOrder')
            ->getQuery()
            ->getResult()
            ;
    }

    public function findByExternalPlayingId($externalPlayingId) {
        return $this->createQueryBuilder('plu')
            ->select('u.firstName, u.lastName, plu.nbButs, plu.sp, plu.nbPassD, plu.sp, plu.nbCartonJ, plu.nbCartonR')
            ->leftJoin('plu.user', 'u')
            ->leftJoin('u.userPoste', 'po')
            ->andWhere('plu.external_playing_id = :externalPlayingId')
            ->setParameter('externalPlayingId', $externalPlayingId)
            ->orderBy('po.zOrder')
            ->getQuery()
            ->getResult()
            ;
    }

    public function findBySeasonAndCategorie($season, $category): array
    {
        return $this->createQueryBuilder('plu')
            ->leftJoin('plu.user', 'u')
            ->leftJoin('u.categorySeasons', 'cs')
            ->andWhere('cs.category = :category')
            ->setParameter('category', $category)
            ->andWhere('plu.season = :season')
            ->setParameter('season', $season)
            ->getQuery()
            ->getResult()
        ;
    }

//    public function findOneBySomeField($value): ?PlayingUser
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
