<?php

namespace App\Entity\Venue;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="venue_foursquare")
 * @ORM\Entity(repositoryClass="App\Repository\Venue\FoursquareRepository")
 */
class Foursquare
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
    private $foursquare_id;
    
    /**
     * @ORM\Column(type="string")
     */
    private $foursquare_url;
}
