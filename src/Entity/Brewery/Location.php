<?php

namespace App\Entity\Brewery;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="brewery_location")
 * @ORM\Entity(repositoryClass="App\Repository\Brewery\LocationRepository")
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
    private $brewery_address;
    
    /**
     * @ORM\Column(type="string")
     */
    private $brewery_city;
    
    /**
     * @ORM\Column(type="string")
     */
    private $brewery_state;
    
    /**
     * @ORM\Column(type="decimal")
     */
    private $brewery_lat;
    
    /**
     * @ORM\Column(type="decimal")
     */
    private $brewery_lng;
    
}
