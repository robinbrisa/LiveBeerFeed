<?php

namespace App\Repository\Beer;

use App\Entity\Beer\LocalBeer;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method LocalBeer|null find($id, $lockMode = null, $lockVersion = null)
 * @method LocalBeer|null findOneBy(array $criteria, array $orderBy = null)
 * @method LocalBeer[]    findAll()
 * @method LocalBeer[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class LocalBeerRepository extends ServiceEntityRepository
{
    public function __construct(\Doctrine\Common\Persistence\ManagerRegistry $registry)
    {
        parent::__construct($registry, LocalBeer::class);
    }

    // /**
    //  * @return LocalBeer[] Returns an array of LocalBeer objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('l')
            ->andWhere('l.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('l.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?LocalBeer
    {
        return $this->createQueryBuilder('l')
            ->andWhere('l.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
