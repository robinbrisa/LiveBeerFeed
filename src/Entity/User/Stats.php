<?php

namespace App\Entity\User;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="user_stats")
 * @ORM\Entity(repositoryClass="App\Repository\User\StatsRepository")
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
    private $total_badges;
    
    /**
     * @ORM\Column(type="integer")
     */
    private $total_friends;
    
    /**
     * @ORM\Column(type="integer")
     */
    private $total_checkins;
    
    /**
     * @ORM\Column(type="integer")
     */
    private $total_beers;
    
    /**
     * @ORM\Column(type="integer")
     */
    private $total_created_beers;
    
    /**
     * @ORM\Column(type="integer")
     */
    private $total_followings;
    
    /**
     * @ORM\Column(type="integer")
     */
    private $total_photos;
}
