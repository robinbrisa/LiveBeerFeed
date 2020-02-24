<?php

namespace App\Repository\Badge;

use App\Entity\Badge\BadgeRelation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method BadgeRelation|null find($id, $lockMode = null, $lockVersion = null)
 * @method BadgeRelation|null findOneBy(array $criteria, array $orderBy = null)
 * @method BadgeRelation[]    findAll()
 * @method BadgeRelation[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class BadgeRelationRepository extends ServiceEntityRepository
{
    public function __construct(\Doctrine\Common\Persistence\ManagerRegistry $registry)
    {
        parent::__construct($registry, BadgeRelation::class);
    }

    /*
    public function findBySomething($value)
    {
        return $this->createQueryBuilder('b')
            ->where('b.something = :value')->setParameter('value', $value)
            ->orderBy('b.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */
}
