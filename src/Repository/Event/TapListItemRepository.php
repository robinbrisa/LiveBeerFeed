<?php

namespace App\Repository\Event;

use App\Entity\Event\TapListItem;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method TapListItem|null find($id, $lockMode = null, $lockVersion = null)
 * @method TapListItem|null findOneBy(array $criteria, array $orderBy = null)
 * @method TapListItem[]    findAll()
 * @method TapListItem[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TapListItemRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, TapListItem::class);
    }
        
    public function getOutOfStockBeers($event) {
        return $this->createQueryBuilder('tli')
        ->join('tli.beer', 'b')
        ->join('tli.session', 's')
        ->join('s.event', 'e')
        ->andWhere('e.id = :event')
        ->setParameter('event', $event)
        ->andWhere('tli.out_of_stock = 1')
        ->getQuery()->getResult();
    }
    
    public function getEventTapList($event, $publisher = null) {
        $qb = $this->createQueryBuilder('tli')
        ->join('tli.beer', 'b')
        ->join('tli.session', 's')
        ->join('s.event', 'e')
        ->andWhere('e.id = :event')
        ->setParameter('event', $event);
        if (!is_null($publisher)) {
            $qb->andWhere('tli.owner = :publisher')
            ->setParameter('publisher', $publisher);
        }
        return $qb->getQuery()->getResult();
    }
}
