<?php

namespace App\Repository;

use App\Entity\Competition;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\ParameterType;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Competition>
 *
 * @method Competition|null find($id, $lockMode = null, $lockVersion = null)
 * @method Competition|null findOneBy(array $criteria, array $orderBy = null)
 * @method Competition[]    findAll()
 * @method Competition[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CompetitionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Competition::class);
    }

    /**
     * @return Competition[] Returns an array of Competition objects
     */
    public function findSeasons(): array
    {
        return $this->createQueryBuilder('c')
            ->select("DISTINCT c.season")
            ->orderBy("c.seasonx", "DESC")
            ->getQuery()
            ->getResult()
        ;
    }

    public function findCategoriesBySeason($season) {
        return $this->createQueryBuilder('c')
            ->select("DISTINCT ca.name")
            ->leftJoin('c.category','ca')
            ->andWhere('c.seasonx = :val')
            ->setParameter('val', $season["season"]->getId()->toBinary(), ParameterType::BINARY)
            ->getQuery()
            ->getResult()
            ;
    }

//    public function findOneBySomeField($value): ?Competition
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
