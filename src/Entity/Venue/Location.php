<?php

namespace App\Entity\Venue;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="venue_location")
 * @ORM\Entity(repositoryClass="App\Repository\Venue\LocationRepository")
 */
class Location
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
    private $venue_address;
    
    /**
     * @ORM\Column(type="string")
     */
    private $venue_city;
    
    /**
     * @ORM\Column(type="string")
     */
    private $venue_state;
    
    /**
     * @ORM\Column(type="string")
     */
    private $venue_country;
    
    /**
     * @ORM\Column(type="decimal")
     */
    private $lat;
    
    /**
     * @ORM\Column(type="decimal")
     */
    private $lng;
}
