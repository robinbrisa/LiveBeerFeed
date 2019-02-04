<?php

namespace App\Repository\Beer;

use App\Entity\Beer\Vintage;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Vintage|null find($id, $lockMode = null, $lockVersion = null)
 * @method Vintage|null findOneBy(array $criteria, array $orderBy = null)
 * @method Vintage[]    findAll()
 * @method Vintage[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class VintageRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Vintage::class);
    }

    /*
    public function findBySomething($value)
    {
        return $this->createQueryBuilder('v')
            ->where('v.something = :value')->setParameter('value', $value)
            ->orderBy('v.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */
}
