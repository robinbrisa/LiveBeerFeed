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

    public function findCurrentEvents()
    {
        return $this->createQueryBuilder('e')
        ->where('e.start_date < :start_date')->setParameter('start_date', new \DateTime(''))
        ->andWhere('e.end_date >= :end_date')->setParameter('end_date', new \DateTime(''))
            ->orderBy('e.id', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }
}
