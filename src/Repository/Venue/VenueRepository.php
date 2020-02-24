<?php

namespace App\Repository\Venue;

use App\Entity\Venue\Venue;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Venue|null find($id, $lockMode = null, $lockVersion = null)
 * @method Venue|null findOneBy(array $criteria, array $orderBy = null)
 * @method Venue[]    findAll()
 * @method Venue[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class VenueRepository extends ServiceEntityRepository
{
    public function __construct(\Doctrine\Common\Persistence\ManagerRegistry $registry)
    {
        parent::__construct($registry, Venue::class);
    }
    
    public function getMostVisitedVenue($uid)
    {
        $qb = $this->createQueryBuilder('v')
        ->select('v, COUNT(c) as total')
        ->join('v.checkins', 'c');
        if (!is_null($uid)) {
            $qb->where('c.user = :id')->setParameter('id', $uid);
        };
        return $qb->groupBy('c.venue')
        ->orderBy('total', 'DESC')
        ->setMaxResults(1)
        ->getQuery()
        ->getSingleResult();
    }
}
