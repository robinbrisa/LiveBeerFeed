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
    public function __construct(\Doctrine\Common\Persistence\ManagerRegistry $registry)
    {
        parent::__construct($registry, Beer::class);
    }
    
    public function getUniqueCheckedInBeersCount($uid = null, $venues = null, $minDate = null, $maxDate = null)
    {
        $qb = $this->createQueryBuilder('b')
        ->select('COUNT(DISTINCT b) AS total')
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
        return $qb->orderBy('total', 'DESC')
        ->setMaxResults(1)
        ->getQuery()
        ->getSingleScalarResult();
    }
    
    public function getUniqueLatestCheckedInBeers($limit = 10, $uid = null, $brewery = null, $venues = null, $minDate = null, $maxDate = null, $withLabels = false) {
        $qb = $this->createQueryBuilder('b')
        ->select('DISTINCT b')
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
        if (!is_null($brewery)) {
            $qb->andWhere('b.brewery = :brewery')->setParameter('brewery', $brewery);
        };
        if ($withLabels) {
            $qb->andWhere($qb->expr()->notLike('b.label', $qb->expr()->literal('%badge-beer-default%')));
        };
        return $qb->orderBy('c.created_at', 'DESC')
        ->setMaxResults($limit)
        ->getQuery()
        ->getResult();
    }
    
    public function getMostCheckedInBeer($uid = null, $venues = null, $minDate = null, $maxDate = null, $limit = 1, $distinct = false) {
        $qb = $this->createQueryBuilder('b');
        if ($distinct) {
            $qb->select('b, w, COUNT(DISTINCT c.user) AS total');
        } else {
            $qb->select('b, w, COUNT(c.user) AS total');
        }
        $qb->join('b.checkins', 'c')
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
        $qb->groupBy('c.beer')
        ->orderBy('total', 'DESC')
        ->setMaxResults($limit);
        if ($limit > 1) {
            return $qb->getQuery()->getResult();
        } else {
            return $qb->getQuery()->getOneOrNullResult();
        }
    }
    
    public function getBestRatedBeer($uid = null, $venues = null, $minDate = null, $maxDate = null, $minCheckins = null, $limit = 1, $distinct = false) {
        $qb = $this->createQueryBuilder('b');
        if ($distinct) {
            $qb->select('b, AVG(c.rating_score) AS avg_rating, COUNT(DISTINCT c.user) AS total');
        } else {
            $qb->select('b, AVG(c.rating_score) AS avg_rating, COUNT(c) AS total');
        }
        $qb->join('b.checkins', 'c');
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
        $qb->groupBy('b.id')
        ->orderBy('avg_rating', 'DESC')
        ->setMaxResults($limit);
        if ($limit > 1) {
            return $qb->getQuery()->getResult();
        } else {
            return $qb->getQuery()->getOneOrNullResult();
        }
    }
        
}
