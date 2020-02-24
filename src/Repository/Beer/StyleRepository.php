<?php

namespace App\Repository\Beer;

use App\Entity\Beer\Style;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Doctrine\ORM\Query\ResultSetMapping;
use Doctrine\ORM\Query\ResultSetMappingBuilder;

/**
 * @method Style|null find($id, $lockMode = null, $lockVersion = null)
 * @method Style|null findOneBy(array $criteria, array $orderBy = null)
 * @method Style[]    findAll()
 * @method Style[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class StyleRepository extends ServiceEntityRepository
{
    public function __construct(\Doctrine\Common\Persistence\ManagerRegistry $registry)
    {
        parent::__construct($registry, Style::class);
    }
    
    public function findAll()
    {
        return $this->findBy(array(), array('name' => 'ASC'));
    }
    
    public function getMostCheckedInStyle($uid = null, $venues = null, $minDate = null, $maxDate = null, $limit = 1) {
        $qb = $this->createQueryBuilder('s')
        ->select('s, b, COUNT(c) AS total')
        ->join('s.beers', 'b')
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
        $qb->groupBy('b.style')
        ->orderBy('total', 'DESC')
        ->setMaxResults($limit);
        if ($limit > 1) {
            return $qb->getQuery()->getResult();
        } else {
            return $qb->getQuery()->getOneOrNullResult();
        }
    }
    
    public function getBestRatedStyle($uid = null, $venues = null, $minDate = null, $maxDate = null, $minCheckins = null, $limit = 1) {
        $qb = $this->createQueryBuilder('s')
        ->select('s, b, AVG(c.rating_score) AS avg_rating, COUNT(c) AS total')
        ->join('s.beers', 'b')
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
        $qb->groupBy('b.style')
        ->orderBy('avg_rating', 'DESC')
        ->setMaxResults($limit);
        if ($limit > 1) {
            return $qb->getQuery()->getResult();
        } else {
            return $qb->getQuery()->getOneOrNullResult();
        }
    }
    
    public function getMostCheckedInStyleUniqueBeers($uid = null, $venues = null, \DateTime $minDate = null, \DateTime $maxDate = null)
    {
        $em = $this->getEntityManager();
        $rsm = new ResultSetMapping();
        $rsm->addScalarResult('total', 'total');
        $rsm->addEntityResult('\App\Entity\Beer\Style', 's');
        $rsm->addFieldResult('s','id','id');
        $rsm->addFieldResult('s','name','name');
        $sql = 'SELECT COUNT(*) total, s.id, s.name ' .
            'FROM ( ' .
            'SELECT b.* ' .
            'FROM beer b ' .
            'JOIN checkin c ON c.beer_id = b.id ' .
            'WHERE 1 = 1 ';
        if (!is_null($uid)) {
            if (is_object($uid)) {
                $uid = $uid->getId();
            }
            $sql .= 'AND c.user_id = ' . $uid . ' ';
        };
        if (!is_null($venues)) {
            $venuesArray = array();
            foreach ($venues as $venue) {
                $venuesArray[] = $venue->getId();
            }
            $sql .= 'AND c.venue_id IN (' . implode(",", $venuesArray) . ') ';
        };
        if (!is_null($minDate)) {
            $sql .= 'AND c.created_at >= "' . $minDate->format('Y-m-d H:i:s') . '" ';
        };
        if (!is_null($maxDate)) {
            $sql .= 'AND c.created_at <= "' . $maxDate->format('Y-m-d H:i:s') . '" ';
        };
        $sql .= 'GROUP BY c.beer_id' .
            ') sub ' .
            'JOIN beer_style s ON sub.style_id = s.id ' .
            'GROUP BY sub.style_id ' . 
            'ORDER BY total DESC ' .
            'LIMIT 1 ';
        $query = $em->createNativeQuery($sql, $rsm);
        return $query->getOneOrNullResult();
    }
}
