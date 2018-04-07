<?php

namespace App\Repository\Brewery;

use App\Entity\Brewery\Brewery;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Brewery|null find($id, $lockMode = null, $lockVersion = null)
 * @method Brewery|null findOneBy(array $criteria, array $orderBy = null)
 * @method Brewery[]    findAll()
 * @method Brewery[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class BreweryRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Brewery::class);
    }
    
    public function getMostCheckedInBrewery($uid = null, $venues = null, $minDate = null, $maxDate = null)
    {
        $qb = $this->createQueryBuilder('w')
        ->select('w, b, COUNT(c) AS total')
        ->join('w.beers', 'b')
        ->join('b.checkins', 'c');
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
        return $qb->groupBy('b.brewery')
        ->orderBy('total', 'DESC')
        ->setMaxResults(1)
        ->getQuery()
        ->getOneOrNullResult();
    }
    
    public function getBestRatedBrewery($uid = null, $venues = null, $minDate = null, $maxDate = null, $minCheckins = null) {
        $qb = $this->createQueryBuilder('w')
        ->select('w, AVG(c.rating_score) AS avg_rating, COUNT(c) AS total')
        ->join('w.beers', 'b')
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
        return $qb->groupBy('b.brewery')
        ->orderBy('avg_rating', 'DESC')
        ->setMaxResults(1)
        ->getQuery()
        ->getSingleResult();
    }
    
    
}
