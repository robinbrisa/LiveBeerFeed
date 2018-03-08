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
    
    public function getTotalToastsToUser($id)
    {
        return $this->createQueryBuilder('t')
        ->select('COUNT(t)')
        ->join('t.checkin', 'c')
        ->where('c.user = :id')->setParameter('id', $id)
        ->getQuery()
        ->getSingleScalarResult()
        ;
    }
    
    public function getTotalToastsByUser($id)
    {
        return $this->createQueryBuilder('t')
        ->select('COUNT(t)')
        ->where('t.user = :id')->setParameter('id', $id)
        ->getQuery()
        ->getSingleScalarResult()
        ;
    }
    
}
