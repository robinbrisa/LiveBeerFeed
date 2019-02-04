<?php

namespace App\Repository\Event;

use App\Entity\Event\Publisher;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Publisher|null find($id, $lockMode = null, $lockVersion = null)
 * @method Publisher|null findOneBy(array $criteria, array $orderBy = null)
 * @method Publisher[]    findAll()
 * @method Publisher[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PublisherRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Publisher::class);
    }

    public function findPublishersToNotifyByEmail() {
        $qb = $this->createQueryBuilder('p')
        ->andWhere('p.notified = :notified')->setParameter('notified', 0)
        ->andWhere('p.email IS NOT NULL')
        ->getQuery()
        ->getResult();
        
        $publishers = array();
        foreach ($qb as $publisher) {
            $publishers[$publisher->getEvent()->getId()][$publisher->getEmail()][] = $publisher;
        }
        return $publishers;
    }
    
//    /**
//     * @return Publisher[] Returns an array of Publisher objects
//     */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('p.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Publisher
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
