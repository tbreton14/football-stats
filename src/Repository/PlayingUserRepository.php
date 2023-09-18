<?php

namespace App\Repository;

use App\Entity\PlayingUser;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

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
