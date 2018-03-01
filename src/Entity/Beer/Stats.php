<?php

namespace App\Entity\Beer;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="beer_stats")
 * @ORM\Entity(repositoryClass="App\Repository\Beer\StatsRepository")
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
    private $total_user_count;
    
}
