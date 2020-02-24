<?php

namespace App\Repository\Search;

use App\Entity\Search\Query;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Query|null find($id, $lockMode = null, $lockVersion = null)
 * @method Query|null findOneBy(array $criteria, array $orderBy = null)
 * @method Query[]    findAll()
 * @method Query[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class QueryRepository extends ServiceEntityRepository
{
    public function __construct(\Doctrine\Common\Persistence\ManagerRegistry $registry)
    {
        parent::__construct($registry, Query::class);
    }

    public function findPending()
    {
        return $this->createQueryBuilder('q')
            ->andWhere('q.finished = :val')
            ->setParameter('val', false)
            ->orderBy('q.id', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }
    

    /*
    public function findOneBySomeField($value): ?Query
    {
        return $this->createQueryBuilder('q')
            ->andWhere('q.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
