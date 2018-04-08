<?php

namespace App\Repository\Beer;

use App\Entity\Beer\Beer;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Beer|null find($id, $lockMode = null, $lockVersion = null)
 * @method Beer|null findOneBy(array $criteria, array $orderBy = null)
 * @method Beer[]    findAll()
 * @method Beer[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class BeerRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Beer::class);
    }
    
    public function getMostCheckedInBeer($uid = null, $venues = null, $minDate = null, $maxDate = null)
    {
        $qb = $this->createQueryBuilder('b')
        ->select('b, w, COUNT(c) AS total')
        ->join('b.checkins', 'c')
        ->join('b.brewery', 'w');
        if (!is_null($uid)) {
            $qb->where('c.user = :id')->setParameter('id', $uid);
        };
        if (!is_null($venues)) {
            $qb->andWhere('c.venue IN (:venues)')->setParameter('venues', $venues);
        };
        if (!is_null($minDate)) {
            $qb->andWhere('c.created_at >= :minDate')->setParameter('minDate', $minDate);
        };
        if (!is_null($maxDate)) {
            $qb->andWhere('c.created_at <= :maxDate')->setParameter('maxDate', $maxDate);
        };
        return $qb->groupBy('c.beer')
        ->orderBy('total', 'DESC')
        ->setMaxResults(1)
        ->getQuery()
        ->getOneOrNullResult();
    }
    
    public function getBestRatedBeer($uid = null, $venues = null, $minDate = null, $maxDate = null, $minCheckins = null) {
        $qb = $this->createQueryBuilder('b')
        ->select('b, AVG(c.rating_score) AS avg_rating, COUNT(c) AS total')
        ->join('b.checkins', 'c');
        if (!is_null($uid)) {
            $qb->andWhere('c.user = :id')->setParameter('id', $uid);
        };
        if (!is_null($venues)) {
            $qb->andWhere('c.venue IN (:venues)')->setParameter('venues', $venues);
        };
        if (!is_null($minDate)) {
            $qb->andWhere('c.created_at >= :minDate')->setParameter('minDate', $minDate);
        };
        if (!is_null($maxDate)) {
            $qb->andWhere('c.created_at <= :maxDate')->setParameter('maxDate', $maxDate);
        };
        if (!is_null($minCheckins)) {
            $qb->having('total > :minCheckins')->setParameter('minCheckins', $minCheckins);
        };
        return $qb->groupBy('b.id')
        ->orderBy('avg_rating', 'DESC')
        ->setMaxResults(1)
        ->getQuery()
        ->getSingleResult();
    }
    
}
