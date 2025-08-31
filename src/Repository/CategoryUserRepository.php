<?php

namespace App\Repository;

use App\Entity\Categoryuser;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Uid\Uuid;

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
    public function findByCategoryAndSeasonName($category,$season)
    {
        return $this->createQueryBuilder('cu')
            ->addSelect('c,u')
            ->leftJoin('cu.category','c')
            ->leftJoin('cu.user','u')
            ->andWhere('cu.category = :val')
            ->setParameter('val', $category)
            ->andWhere('cu.season = :val2')
            ->setParameter('val2', $season)
            ->getQuery()
            ->getArrayResult()
        ;
    }

    public function findUsersBySeasonAndCategorie($season, $category): array
    {
        return $this->createQueryBuilder('c')
            ->addSelect('u,p')
            ->leftJoin("c.users","u")
            ->leftJoin("u.userPoste","p")
            ->leftJoin("c.seasonx","s")
            ->andWhere('c.category = :category')
            ->setParameter('category', $category)
            ->andWhere("s.id = :season")
            ->setParameter("season", $season)
            ->orderBy('p.zOrder','asc')
            ->getQuery()
            ->getResult()
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
