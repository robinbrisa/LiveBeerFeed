<?php

namespace App\Repository\Checkin;

use App\Entity\Checkin\Checkin;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

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
        ->orderBy('COUNT(c)', 'DESC')
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
        ->orderBy('COUNT(c)', 'DESC')
        ->setMaxResults(1)
        ->getQuery()
        ->getSingleResult();
    }
    
    public function getMostCheckedInUniqueBrewery($uid = null)
    {
        /*$subquery = $this->createQueryBuilder('c')
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
