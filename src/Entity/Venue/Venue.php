<?php

namespace App\Entity\Venue;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="venue")
 * @ORM\Entity(repositoryClass="App\Repository\Venue\VenueRepository")
 */
class Venue
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     */
    private $id;
    
    /**
     * @ORM\Column(type="string")
     */
    private $venue_name;
    
    /**
     * @ORM\Column(type="string")
     */
    private $venue_slug;
    
    /**
     * @ORM\Column(type="datetime")
     */
    private $last_updated;
    
    /**
     * @ORM\ManyToMany(targetEntity="Category", inversedBy="venues")
     * @ORM\JoinTable(name="rel_venue_category")
     */
    private $categories;
    
    /**
     * @ORM\OneToOne(targetEntity="Stats")
     * @ORM\JoinColumn(name="stats_id", referencedColumnName="id")
     */
    private $stats;
    
    /**
     * @ORM\OneToOne(targetEntity="Icon")
     * @ORM\JoinColumn(name="icon_id", referencedColumnName="id")
     */
    private $venue_icon;
    
    /**
     * @ORM\Column(type="boolean")
     */
    private $public_venue;
    
    /**
     * @ORM\OneToOne(targetEntity="Location")
     * @ORM\JoinColumn(name="location_id", referencedColumnName="id")
     */
    private $location;
    
    /**
     * @ORM\OneToOne(targetEntity="Contact")
     * @ORM\JoinColumn(name="contact_id", referencedColumnName="id")
     */
    private $contact;
    
    /**
     * @ORM\OneToOne(targetEntity="Foursquare")
     * @ORM\JoinColumn(name="foursquare_id", referencedColumnName="id")
     */
    private $foursquare;
    
    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Checkin\Checkin", mappedBy="venue")
     */
    private $checkins;
}
