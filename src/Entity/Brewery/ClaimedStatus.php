<?php

namespace App\Entity\Brewery;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="brewery_claimed_status")
 * @ORM\Entity(repositoryClass="App\Repository\Brewery\ClaimedStatusRepository")
 */
class ClaimedStatus
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;
    
    /**
     * @ORM\Column(type="boolean")
     */
    private $is_claimed;
    
    /**
     * @ORM\Column(type="string")
     */
    private $claimed_slug;
    
    /**
     * @ORM\Column(type="integer")
     */
    private $follower_count;
    
    /**
     * @ORM\Column(type="integer")
     */
    private $uid;
    
    /**
     * @ORM\Column(type="string")
     */
    private $mute_status;
    
}
