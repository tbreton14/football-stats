<?php

namespace App\Repository;

use App\Entity\Playing;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Playing>
 *
 * @method Playing|null find($id, $lockMode = null, $lockVersion = null)
 * @method Playing|null findOneBy(array $criteria, array $orderBy = null)
 * @method Playing[]    findAll()
 * @method Playing[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PlayingRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Playing::class);
    }

//    /**
//     * @return Playing[] Returns an array of Playing objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('p.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Playing
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
