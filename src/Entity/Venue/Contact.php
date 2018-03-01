<?php

namespace App\Entity\Venue;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="venue_contact")
 * @ORM\Entity(repositoryClass="App\Repository\Venue\ContactRepository")
 */
class Contact
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
    private $twitter;
    
    /**
     * @ORM\Column(type="string")
     */
    private $venue_url;
    
    /**
     * @ORM\Column(type="string")
     */
    private $facebook;
    
    /**
     * @ORM\Column(type="string")
     */
    private $yelp;
}
