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
    
    public function getTotalCheckinsCount($uid = null, $venues = null, $minDate = null, $maxDate = null)
    {
        $qb = $this->createQueryBuilder('c')
        ->select('c, COUNT(c) AS total');
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
        $result = $qb->getQuery()
        ->getOneOrNullResult();
        
        if ($result) {
            return $result['total'];
        } else {
            return false;
        }
    }
    
    public function getCheckinWithMostToasts($uid = null, $venues = null, $minDate = null, $maxDate = null)
    {
        $qb = $this->createQueryBuilder('c')
        ->select('c');
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
        return $qb->addOrderBy('c.total_toasts', 'DESC')
        ->addOrderBy('c.created_at', 'DESC')
        ->setMaxResults(1)
        ->getQuery()
        ->getOneOrNullResult();
    }
    
    public function getCheckinWithMostComments($uid = null, $venues = null, $minDate = null, $maxDate = null)
    {
        $qb = $this->createQueryBuilder('c')
        ->select('c');
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
        return $qb->addOrderBy('c.total_comments', 'DESC')
        ->addOrderBy('c.created_at', 'DESC')
        ->setMaxResults(1)
        ->getQuery()
        ->getOneOrNullResult();
    }
    
    public function getCheckinWithMostBadges($uid = null, $venues = null, $minDate = null, $maxDate = null)
    {
        $qb = $this->createQueryBuilder('c')
        ->select('c');
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
        return $qb->addOrderBy('c.total_badges', 'DESC')
        ->addOrderBy('c.created_at', 'DESC')
        ->setMaxResults(1)
        ->getQuery()
        ->getOneOrNullResult();
    }
    
    public function getAverageRatingByCheckin($uid = null, $venues = null, $minDate = null, $maxDate = null)
    {
        $qb = $this->createQueryBuilder('c')
        ->select('AVG(c.rating_score) AS average, COUNT(c) AS total')
        ->where('c.rating_score IS NOT NULL');
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
        return $qb->getQuery()
        ->getOneOrNullResult();
    }
    
    public function getNoRatingCheckinsCount($uid = null, $venues = null, $minDate = null, $maxDate = null)
    {
        $qb = $this->createQueryBuilder('c')
        ->select('COUNT(c) AS total')
        ->where('c.rating_score IS NULL');
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
        return $qb->getQuery()
        ->getOneOrNullResult();
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
    
    public function getVenueCheckins($venues, $minID = null, $limit = null)
    {
        $qb = $this->createQueryBuilder('c')
        ->select('c, m, v, u, b')
        ->join('c.venue', 'v')
        ->join('c.user', 'u')
        ->join('c.beer', 'b')
        ->leftJoin('c.medias', 'm')
        ->where('c.venue IN (:vid)')->setParameter('vid', $venues);
        if (!is_null($minID)) {
            $qb->andWhere('c.id > :minid')->setParameter('minid', $minID);
        }
        $qb->orderBy('c.created_at', 'DESC');
        if (!is_null($limit)) {
            $qb->setMaxResults($limit);
        }
        return $qb->getQuery()
        ->getResult();
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
