<?php

namespace App\Entity\Brewery;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="brewery_stats")
 * @ORM\Entity(repositoryClass="App\Repository\Brewery\StatsRepository")
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
    private $unique_count;
    
    /**
     * @ORM\Column(type="integer")
     */
    private $monthly_count;
    
    /**
     * @ORM\Column(type="integer")
     */
    private $weekly_count;
    
    /**
     * @ORM\Column(type="decimal")
     */
    private $age_on_service;
    
}
