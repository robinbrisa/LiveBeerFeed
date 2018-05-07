<?php

namespace App\Repository\User;

use App\Entity\User\SavedData;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method UserSavedData|null find($id, $lockMode = null, $lockVersion = null)
 * @method UserSavedData|null findOneBy(array $criteria, array $orderBy = null)
 * @method UserSavedData[]    findAll()
 * @method UserSavedData[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SavedDataRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, SavedData::class);
    }

//    /**
//     * @return UserSavedData[] Returns an array of UserSavedData objects
//     */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('u.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?UserSavedData
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
