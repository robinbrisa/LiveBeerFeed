<?php

namespace App\Repository\Checkin;

use App\Entity\Checkin\Comment;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Comment|null find($id, $lockMode = null, $lockVersion = null)
 * @method Comment|null findOneBy(array $criteria, array $orderBy = null)
 * @method Comment[]    findAll()
 * @method Comment[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CommentRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Comment::class);
    }
    
    public function getTotalCommentsToUser($id)
    {
        return $this->createQueryBuilder('com')
        ->select('COUNT(com)')
        ->join('com.checkin', 'c')
        ->where('c.user = :id')->setParameter('id', $id)
        ->getQuery()
        ->getSingleScalarResult()
        ;
    }
    
    public function getTotalCommentsByUser($id)
    {
        return $this->createQueryBuilder('com')
        ->select('COUNT(com)')
        ->where('com.user = :id')->setParameter('id', $id)
        ->getQuery()
        ->getSingleScalarResult();
    }
}
