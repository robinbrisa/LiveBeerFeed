<?php

namespace App\Repository\Checkin;

use App\Entity\Checkin\Toast;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Toast|null find($id, $lockMode = null, $lockVersion = null)
 * @method Toast|null findOneBy(array $criteria, array $orderBy = null)
 * @method Toast[]    findAll()
 * @method Toast[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ToastRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Toast::class);
    }

    /*
    public function findBySomething($value)
    {
        return $this->createQueryBuilder('t')
            ->where('t.something = :value')->setParameter('value', $value)
            ->orderBy('t.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */
}
