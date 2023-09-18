<?php

namespace App\Repository;

use App\Entity\Categoryuser;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Categoryuser>
 *
 * @method Categoryuser|null find($id, $lockMode = null, $lockVersion = null)
 * @method Categoryuser|null findOneBy(array $criteria, array $orderBy = null)
 * @method Categoryuser[]    findAll()
 * @method Categoryuser[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CategoryUserRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Categoryuser::class);
    }

    /**
     * @return Categoryuser[] Returns an array of Categoryuser objects
     */
    public function findByCategoryName($value)
    {
        return $this->createQueryBuilder('cu')
            ->addSelect('c,u')
            ->leftJoin('cu.category','c')
            ->leftJoin('cu.user','u')
            ->andWhere('c.name = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getArrayResult()
        ;
    }

//    public function findOneBySomeField($value): ?Categoryuser
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
