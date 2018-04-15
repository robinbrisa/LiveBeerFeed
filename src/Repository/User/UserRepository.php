<?php

namespace App\Repository\User;

use App\Entity\User\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, User::class);
    }
    
    public function getUniqueUsersWithCheckinsCount($venues = null, $minDate = null, $maxDate = null)
    {
        $qb = $this->createQueryBuilder('u')
        ->select('COUNT(DISTINCT u) AS total')
        ->join('u.checkins', 'c');
        if (!is_null($venues)) {
            $qb->andWhere('c.venue IN (:venues)')->setParameter('venues', $venues);
        };
        if (!is_null($minDate)) {
            $qb->andWhere('c.created_at >= :minDate')->setParameter('minDate', $minDate);
        };
        if (!is_null($maxDate)) {
            $qb->andWhere('c.created_at <= :maxDate')->setParameter('maxDate', $maxDate);
        };
        return $qb->orderBy('total', 'DESC')
        ->setMaxResults(1)
        ->getQuery()
        ->getSingleScalarResult();
    }
    
    public function getUniqueLatestCheckInUsers($limit = 10, $venues = null, $minDate = null, $maxDate = null, $withAvatar = false) {
        $qb = $this->createQueryBuilder('u')
        ->select('DISTINCT u')
        ->join('u.checkins', 'c');
        if (!is_null($venues)) {
            $qb->andWhere('c.venue IN (:venues)')->setParameter('venues', $venues);
        };
        if (!is_null($minDate)) {
            $qb->andWhere('c.created_at >= :minDate')->setParameter('minDate', $minDate);
        };
        if (!is_null($maxDate)) {
            $qb->andWhere('c.created_at <= :maxDate')->setParameter('maxDate', $maxDate);
        };
        if ($withAvatar) {
            $qb->andWhere($qb->expr()->notLike('u.user_avatar', $qb->expr()->literal('%default_avatar%')));
        };
        return $qb->orderBy('c.created_at', 'DESC')
        ->setMaxResults($limit)
        ->getQuery()
        ->getResult();
    }
    
    public function getMostCheckinsCount($uid = null, $venues = null, $minDate = null, $maxDate = null)
    {
        $qb = $this->createQueryBuilder('u')
        ->select('u, COUNT(c) AS total')
        ->join('u.checkins', 'c');
        if (!is_null($uid)) {
            $qb->andWhere('c.user = :id')->setParameter('id', $uid);
        };
        if (!is_null($venues)) {
            $qb->andWhere('c.venue IN (:venues)')->setParameter('venues', $venues);
        };
        if (!is_null($minDate)) {
            $qb->andWhere('c.created_at >= :minDate')->setParameter('minDate', $minDate);
        };
        if (!is_null($maxDate)) {
            $qb->andWhere('c.created_at <= :maxDate')->setParameter('maxDate', $maxDate);
        };
        return $qb->groupBy('u.id')
        ->orderBy('total', 'DESC')
        ->setMaxResults(1)
        ->getQuery()
        ->getOneOrNullResult();
    }
    
    public function getNoRatingCheckinsCount($uid = null, $venues = null, $minDate = null, $maxDate = null)
    {
        $qb = $this->createQueryBuilder('u')
        ->select('u, COUNT(c) AS total')
        ->join('u.checkins', 'c')
        ->where('c.rating_score IS NULL');
        if (!is_null($uid)) {
            $qb->andWhere('c.user = :id')->setParameter('id', $uid);
        };
        if (!is_null($venues)) {
            $qb->andWhere('c.venue IN (:venues)')->setParameter('venues', $venues);
        };
        if (!is_null($minDate)) {
            $qb->andWhere('c.created_at >= :minDate')->setParameter('minDate', $minDate);
        };
        if (!is_null($maxDate)) {
            $qb->andWhere('c.created_at <= :maxDate')->setParameter('maxDate', $maxDate);
        };
        return $qb->groupBy('u.id')
        ->orderBy('total', 'DESC')
        ->setMaxResults(1)
        ->getQuery()
        ->getOneOrNullResult();
    }
    
    public function getAPIKeys() {
        
        $keysArray = array('default' => 100);
        
        $users = $this->createQueryBuilder('u')
        ->where('u.internal_untappd_access_token IS NOT NULL')
        ->getQuery()
        ->getResult();
        
        foreach ($users as $user) {
            $keysArray[$user->getInternalUntappdAccessToken()] = 100;
        }
        return $keysArray;
    }
}
