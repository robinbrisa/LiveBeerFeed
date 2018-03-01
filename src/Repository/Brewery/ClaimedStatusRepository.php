<?php

namespace App\Repository\Brewery;

use App\Entity\Brewery\ClaimedStatus;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method ClaimedStatus|null find($id, $lockMode = null, $lockVersion = null)
 * @method ClaimedStatus|null findOneBy(array $criteria, array $orderBy = null)
 * @method ClaimedStatus[]    findAll()
 * @method ClaimedStatus[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ClaimedStatusRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, ClaimedStatus::class);
    }

    /*
    public function findBySomething($value)
    {
        return $this->createQueryBuilder('c')
            ->where('c.something = :value')->setParameter('value', $value)
            ->orderBy('c.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */
}
