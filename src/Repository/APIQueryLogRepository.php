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
    public function __construct(\Doctrine\Common\Persistence\ManagerRegistry $registry)
    {
        parent::__construct($registry, APIQueryLog::class);
    }

    public function findUsedAPIKeys()
    {
        $keysArray = array();
        $registeredKeys = $this->createQueryBuilder('a')
            ->addSelect('100-COUNT(a) AS remaining_queries, u.internal_untappd_access_token')
            ->leftJoin('a.user', 'u')
            ->groupBy('u.id')
            ->where('a.date >= :interval')->setParameter('interval', new \DateTime('- 1 hour'))
            ->getQuery()
            ->getResult();
        
        foreach ($registeredKeys as $key) {
            $keyToken = $key['internal_untappd_access_token'];
            if (is_null($keyToken)) {
                $keyToken = "default";
            }
            $keysArray[$keyToken] = intval($key['remaining_queries']);
        }
        return $keysArray;
    }

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
    
    /*
SELECT MAX(q.remaining_queries), u.internal_untappd_access_token
FROM api_query_log q
LEFT JOIN user u ON q.user_key_id = u.id
GROUP BY u.id
UNION
SELECT "100", u.internal_untappd_access_token
FROM user u
LEFT JOIN api_query_log q ON u.id = q.user_key_id
WHERE u.internal_untappd_access_token IS NOT NULL
AND q.id IS NULL;
     */
}
