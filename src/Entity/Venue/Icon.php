<?php

namespace App\Entity\Venue;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="venue_icon")
 * @ORM\Entity(repositoryClass="App\Repository\Venue\IconRepository")
 */
class Icon
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;
    
    /**
     * @ORM\Column(type="string")
     */
    private $sm;
    
    /**
     * @ORM\Column(type="string")
     */
    private $md;
    
    /**
     * @ORM\Column(type="string")
     */
    private $lg;
}
