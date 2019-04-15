<?php

namespace App\Repository\Event;

use App\Entity\Event\Event;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Event|null find($id, $lockMode = null, $lockVersion = null)
 * @method Event|null findOneBy(array $criteria, array $orderBy = null)
 * @method Event[]    findAll()
 * @method Event[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EventRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Event::class);
    }

    public function findCurrentEvents($all = false)
    {
        $qb = $this->createQueryBuilder('e')
        ->where('e.start_date < :start_date')->setParameter('start_date', new \DateTime(''))
        ->andWhere('e.end_date >= :end_date')->setParameter('end_date', new \DateTime(''));
        if ($all) {
            $qb->andWhere('e.hidden != 1');
        }
        return $qb->orderBy('e.start_date', 'DESC')
        ->getQuery()
        ->getResult();
    }
    
    public function findPreviousEvents()
    {
        return $this->createQueryBuilder('e')
        ->where('e.end_date < :end_date')->setParameter('end_date', new \DateTime(''))
        ->andWhere('e.hidden != 1')
        ->orderBy('e.end_date', 'DESC')
        ->getQuery()
        ->getResult();
    }
    
    public function findUpcomingEvents()
    {
        return $this->createQueryBuilder('e')
        ->where('e.start_date > :start_date')->setParameter('start_date', new \DateTime(''))
        ->andWhere('e.hidden != 1')
        ->orderBy('e.start_date', 'ASC')
        ->getQuery()
        ->getResult();
    }
    
    public function getFutureOrCurrentEventsUserIsAttending($user) {
        return $this->createQueryBuilder('e')
        ->join('e.users_attending', 'u')
        ->where('e.end_date > :now')->setParameter('now', new \DateTime())
        ->andWhere('u.id = :user')->setParameter('user', $user)
        ->getQuery()
        ->getResult();
    }
    
    public function getFutureOrCurrentEventsWhereBeerIsAvailable($beer) {
        return $this->createQueryBuilder('e')
        ->join('e.sessions', 's')
        ->join('s.tap_list_items', 'tli')
        ->join('tli.beer', 'b')
        ->where('e.end_date > :now')->setParameter('now', new \DateTime())
        ->andWhere('b.id = :beer')->setParameter('beer', $beer)
        ->getQuery()
        ->getResult();
    }
}
