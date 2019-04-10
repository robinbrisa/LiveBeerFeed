<?php

namespace App\Repository\Event;

use App\Entity\Event\Session;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Session|null find($id, $lockMode = null, $lockVersion = null)
 * @method Session|null findOneBy(array $criteria, array $orderBy = null)
 * @method Session[]    findAll()
 * @method Session[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SessionRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Session::class);
    }
    
    public function getEventSessionsWhereBeerIsAvailable($event, $beer) {
        return $this->createQueryBuilder('s')
        ->join('s.event', 'e')
        ->join('s.tap_list_items', 'tli')
        ->join('tli.beer', 'b')
        ->where('e.id = :event')->setParameter('event', $event)
        ->andWhere('b.id = :beer')->setParameter('beer', $beer)
        ->getQuery()
        ->getResult();
    }
    
    public function findUpcomingSessions()
    {
        return $this->createQueryBuilder('s')
        ->join('s.event', 'e')
        ->where('e.end_date >= :end_date')->setParameter('end_date', new \DateTime(''))
        ->orderBy('e.start_date', 'ASC')
        ->getQuery()
        ->getResult()
        ;
    }

    /*
    public function findOneBySomeField($value): ?Session
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
