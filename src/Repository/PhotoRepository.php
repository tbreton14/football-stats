<?php

namespace App\Repository;

use App\Entity\Category;
use App\Entity\Competition;
use App\Entity\Photo;
use App\Entity\Season;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Photo>
 */
class PhotoRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Photo::class);
    }

    /**
     * @return Photo[]
     */
    public function findBySeasonAndOptionalFilters(?Season $season, ?Category $category = null, ?Competition $competition = null): array
    {
        if (!$season) {
            return [];
        }

        $qb = $this->createQueryBuilder('p')
            ->andWhere('p.season = :season')
            ->setParameter('season', $season)
            ->orderBy('p.createdAt', 'DESC');

        if ($competition) {
            $qb->andWhere('(p.competition = :competition OR p.competition IS NULL)')
                ->setParameter('competition', $competition);
        }

        if ($category) {
            $qb->andWhere('(p.category = :category OR p.category IS NULL)')
                ->setParameter('category', $category);
        }

        return $qb->getQuery()->getResult();
    }
}
