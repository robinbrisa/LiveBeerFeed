<?php

namespace App\Repository\Event;

use App\Entity\Event\TapListQueue;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method TapListQueue|null find($id, $lockMode = null, $lockVersion = null)
 * @method TapListQueue|null findOneBy(array $criteria, array $orderBy = null)
 * @method TapListQueue[]    findAll()
 * @method TapListQueue[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TapListQueueRepository extends ServiceEntityRepository
{
    public function __construct(\Doctrine\Common\Persistence\ManagerRegistry $registry)
    {
        parent::__construct($registry, TapListQueue::class);
    }
    
//    /**
//     * @return TapListQueue[] Returns an array of TapListQueue objects
//     */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('t.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?TapListQueue
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
