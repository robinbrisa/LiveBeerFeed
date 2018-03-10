<?php

namespace App\Repository\Checkin;

use App\Entity\Checkin\Checkin;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;
use DoctrineExtensions\Query\Mysql\Date;

/**
 * @method Checkin|null find($id, $lockMode = null, $lockVersion = null)
 * @method Checkin|null findOneBy(array $criteria, array $orderBy = null)
 * @method Checkin[]    findAll()
 * @method Checkin[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CheckinRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Checkin::class);
    }
    
    public function getTotalCheckinsCount($uid = null)
    {
        $qb = $this->createQueryBuilder('c')
        ->select('c, COUNT(c) AS total');
        if (!is_null($uid)) {
            $qb->where('c.user = :id')->setParameter('id', $uid);
        };
        $result = $qb->getQuery()
        ->getSingleResult();
        
        return $result['total'];
    }
    
    public function getCheckinWithMostToasts($uid = null)
    {
        $qb = $this->createQueryBuilder('c')
        ->select('c');
        if (!is_null($uid)) {
            $qb->where('c.user = :id')->setParameter('id', $uid);
        };
        return $qb->addOrderBy('c.total_toasts', 'DESC')
        ->addOrderBy('c.created_at', 'DESC')
        ->setMaxResults(1)
        ->getQuery()
        ->getSingleResult();
    }
    
    public function getCheckinWithMostComments($uid = null)
    {
        $qb = $this->createQueryBuilder('c')
        ->select('c');
        if (!is_null($uid)) {
            $qb->where('c.user = :id')->setParameter('id', $uid);
        };
        return $qb->addOrderBy('c.total_comments', 'DESC')
        ->addOrderBy('c.created_at', 'DESC')
        ->setMaxResults(1)
        ->getQuery()
        ->getSingleResult();
    }
    
    public function getCheckinWithMostBadges($uid = null)
    {
        $qb = $this->createQueryBuilder('c')
        ->select('c');
        if (!is_null($uid)) {
            $qb->where('c.user = :id')->setParameter('id', $uid);
        };
        return $qb->addOrderBy('c.total_badges', 'DESC')
        ->addOrderBy('c.created_at', 'DESC')
        ->setMaxResults(1)
        ->getQuery()
        ->getSingleResult();
    }
    
    public function getAverageRatingByCheckin($uid = null)
    {
        $qb = $this->createQueryBuilder('c')
        ->select('AVG(c.rating_score)');
        if (!is_null($uid)) {
            $qb->where('c.user = :id')->setParameter('id', $uid);
        };
        return $qb->getQuery()
        ->getSingleScalarResult();
    }
    
    public function getRatingsCountByScore($uid = null)
    {
        $qb = $this->createQueryBuilder('c')
        ->select('COUNT(c) AS total, c.rating_score');
        if (!is_null($uid)) {
            $qb->where('c.user = :id')->setParameter('id', $uid);
        };
        $results = $qb->groupBy('c.rating_score')
        ->getQuery()
        ->getResult();
        
        $output = array();
        foreach ($results as $result) {
            if (is_null($result['rating_score'])) {
                $result['rating_score'] = 'null';
            }
            $output[$result['rating_score']] = $result['total'];
        }
        return $output;
    }
    
    public function getMostCheckedInBeer($uid = null)
    {
        $qb = $this->createQueryBuilder('c')
        ->select('c, COUNT(c) AS total, b');
        if (!is_null($uid)) {
            $qb->where('c.user = :id')->setParameter('id', $uid);
        };
        return $qb->join('c.beer', 'b')
        ->groupBy('c.beer')
        ->orderBy('total', 'DESC')
        ->setMaxResults(1)
        ->getQuery()
        ->getSingleResult();
    }
    
    public function getMostCheckedInBrewery($uid = null)
    {
        $qb = $this->createQueryBuilder('c')
        ->select('c, COUNT(c) AS total, b, w');
        if (!is_null($uid)) {
            $qb->where('c.user = :id')->setParameter('id', $uid);
        };
        return $qb->join('c.beer', 'b')
        ->join('b.brewery', 'w')
        ->groupBy('b.brewery')
        ->orderBy('total', 'DESC')
        ->setMaxResults(1)
        ->getQuery()
        ->getSingleResult();
    }
    
    public function getBestRatedBrewery($uid = null) {
        $qb = $this->createQueryBuilder('c')
        ->select('c, AVG(c.rating_score) AS avg_rating, COUNT(c) AS total, b, w');
        if (!is_null($uid)) {
            $qb->where('c.user = :id')->setParameter('id', $uid);
        };
        return $qb->join('c.beer', 'b')
        ->join('b.brewery', 'w')
        ->groupBy('b.brewery')
        ->orderBy('avg_rating', 'DESC')
        ->setMaxResults(1)
        ->getQuery()
        ->getSingleResult();
    }
    
    public function getCheckinHistoryPerDay($uid = null) {
        $qb = $this->createQueryBuilder('c')
        ->select('c, COUNT(c) as total, DATE(c.created_at) as day');
        if (!is_null($uid)) {
            $qb->where('c.user = :id')->setParameter('id', $uid);
        };
        $results = $qb->groupBy('day')
        ->getQuery()
        ->getResult();
        
        $output = array();
        foreach ($results as $result) {
            $output[$result['day']] = (int)$result['total'];
        }
        return $output;
    }
    
    
    public function getDayWithMostCheckins($uid = null) {
        $qb = $this->createQueryBuilder('c')
        ->select('c, COUNT(c) as total, DATE(c.created_at) as day');
        if (!is_null($uid)) {
            $qb->where('c.user = :id')->setParameter('id', $uid);
        };
        $results = $qb->groupBy('day')
        ->orderBy('total', 'DESC')
        ->setMaxResults(1)
        ->getQuery()
        ->getSingleResult();
        
        $output = array();
        $output[$results['day']] = (int)$results['total'];
        return $output;
    }
    
    public function getMonthWithMostCheckins($uid = null) {
        $qb = $this->createQueryBuilder('c')
        ->select('c, COUNT(c) as total, MONTH(c.created_at) AS mm, YEAR(c.created_at) as yy');
        if (!is_null($uid)) {
            $qb->where('c.user = :id')->setParameter('id', $uid);
        };
        $results = $qb->groupBy('mm')
        ->addGroupBy('yy')
        ->orderBy('total', 'DESC')
        ->setMaxResults(1)
        ->getQuery()
        ->getSingleResult();
        
        $output = array();
        $output[$results['yy'].'-'.$results['mm']] = (int)$results['total'];
        return $output;
    }
    
    public function getYearWithMostCheckins($uid = null) {
        $qb = $this->createQueryBuilder('c')
        ->select('c, COUNT(c) as total, YEAR(c.created_at) as yy');
        if (!is_null($uid)) {
            $qb->where('c.user = :id')->setParameter('id', $uid);
        };
        $results = $qb->groupBy('yy')
        ->orderBy('total', 'DESC')
        ->setMaxResults(1)
        ->getQuery()
        ->getSingleResult();
        
        $output = array();
        $output[$results['yy']] = (int)$results['total'];
        return $output;
    }
    
    public function getVenueCheckins($vid, $limit)
    {
        return $this->createQueryBuilder('c')
        ->select('c')
        ->where('c.venue = :vid')->setParameter('vid', $vid)
        ->orderBy('c.created_at', 'DESC')
        ->setMaxResults($limit)
        ->getQuery()
        ->getResult()
        ;
    }
    
    public function getMostCheckedInUniqueBrewery($uid = null)
    {
        /*
SELECT COUNT(c.id), y.id, y.name
FROM (
	SELECT * FROM checkin
	GROUP BY beer_id
) c
JOIN beer b ON c.beer_id = b.id
JOIN brewery y ON b.brewery_id = y.id
GROUP BY b.brewery_id
ORDER BY COUNT(c.id) DESC
         * 
         * 
         * $subquery = $this->createQueryBuilder('c')
        ->groupBy('c.beer');
        
        $qb = $this->createQueryBuilder('c');
        $query = $qb->select('c, COUNT(c) AS total, b, w');
        $query->where($query->expr()->in('c.id', $subquery));
        if (!is_null($uid)) {
            $qb->andWhere('c.user = :id')->setParameter('id', $uid);
        };
        return $query->join('c.beer', 'b')
        ->join('b.brewery', 'w')
        ->groupBy('b.brewery')
        ->orderBy('COUNT(c)', 'DESC')
        ->setMaxResults(1)
        ->getQuery()
        ->getSingleResult();*/
    }
}
