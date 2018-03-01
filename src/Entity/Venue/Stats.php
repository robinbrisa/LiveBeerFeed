<?php

namespace App\Entity\Venue;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="venue_stats")
 * @ORM\Entity(repositoryClass="App\Repository\Venue\StatsRepository")
 */
class Stats
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;
    
    /**
     * @ORM\Column(type="integer")
     */
    private $total_count;
        
    /**
     * @ORM\Column(type="integer")
     */
    private $monthly_count;
    
    /**
     * @ORM\Column(type="integer")
     */
    private $weekly_count;  
}
