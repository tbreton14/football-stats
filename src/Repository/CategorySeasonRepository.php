<?php

namespace App\Repository;

use App\Entity\CategorySeason;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Uid\Uuid;

/**
 * @extends ServiceEntityRepository<CategorySeason>
 *
 * @method CategorySeason|null find($id, $lockMode = null, $lockVersion = null)
 * @method CategorySeason|null findOneBy(array $criteria, array $orderBy = null)
 * @method CategorySeason[]    findAll()
 * @method CategorySeason[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CategorySeasonRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CategorySeason::class);
    }

//    public function findOneBySomeField($value): ?CategorySeason
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
