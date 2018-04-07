<?php

namespace App\Repository\Event;

use App\Entity\Event\Message;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Message|null find($id, $lockMode = null, $lockVersion = null)
 * @method Message|null findOneBy(array $criteria, array $orderBy = null)
 * @method Message[]    findAll()
 * @method Message[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MessageRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Message::class);
    }

//    /**
//     * @return Message[] Returns an array of Message objects
//     */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('m.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    public function findInfoMessageToDisplay($event): ?Message
    {
        $qb = $this->createQueryBuilder('m');
        $qb->where($qb->expr()->orX(
            $qb->expr()->isNull('m.start_date'),
            $qb->expr()->lte('m.start_date', ':start_date')
        ))->setParameter('start_date', new \DateTime(''))
        ->andWhere($qb->expr()->orX(
            $qb->expr()->isNull('m.end_date'),
            $qb->expr()->gte('m.end_date', ':end_date')
        ));
        return $qb->setParameter('end_date', new \DateTime(''))
        ->andWhere('m.event = :event')->setParameter('event', $event)
        ->orderBy('m.last_time_displayed', 'ASC')
        ->setMaxResults(1)
        ->getQuery()
        ->getOneOrNullResult()
        ;
    }
}
