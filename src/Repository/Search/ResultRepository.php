<?php

namespace App\Repository\Search;

use App\Entity\Search\Result;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Result|null find($id, $lockMode = null, $lockVersion = null)
 * @method Result|null findOneBy(array $criteria, array $orderBy = null)
 * @method Result[]    findAll()
 * @method Result[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ResultRepository extends ServiceEntityRepository
{
    public function __construct(\Doctrine\Common\Persistence\ManagerRegistry $registry)
    {
        parent::__construct($registry, Result::class);
    }
    
    public function findSelectedResults($query)
    {
        return $this->createQueryBuilder('r')
        ->join('r.element', 'e')
        ->join('e.query', 'q')
        ->andWhere('q.id = :query')
        ->andWhere('r.selected = true')
        ->setParameter('query', $query)
        ->getQuery()
        ->getResult()
        ;
    }

    /*
    public function findOneBySomeField($value): ?Result
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
