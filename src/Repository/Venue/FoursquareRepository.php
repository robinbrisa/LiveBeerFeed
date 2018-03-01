<?php

namespace App\Repository\Venue;

use App\Entity\Venue\Foursquare;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Foursquare|null find($id, $lockMode = null, $lockVersion = null)
 * @method Foursquare|null findOneBy(array $criteria, array $orderBy = null)
 * @method Foursquare[]    findAll()
 * @method Foursquare[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FoursquareRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Foursquare::class);
    }

    /*
    public function findBySomething($value)
    {
        return $this->createQueryBuilder('f')
            ->where('f.something = :value')->setParameter('value', $value)
            ->orderBy('f.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */
}
