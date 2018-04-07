<?php

namespace App\Repository;

use App\Entity\APIQueryLog;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method APIQueryLog|null find($id, $lockMode = null, $lockVersion = null)
 * @method APIQueryLog|null findOneBy(array $criteria, array $orderBy = null)
 * @method APIQueryLog[]    findAll()
 * @method APIQueryLog[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class APIQueryLogRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, APIQueryLog::class);
    }

//    /**
//     * @return APIQueryLog[] Returns an array of APIQueryLog objects
//     */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('a.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?APIQueryLog
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
