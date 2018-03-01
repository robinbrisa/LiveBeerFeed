<?php

namespace App\Entity\Brewery;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Table(name="brewery")
 * @ORM\Entity(repositoryClass="App\Repository\Brewery\BreweryRepository")
 */
class Brewery
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
    private $brewery_name;
    
    /**
     * @ORM\Column(type="string")
     */
    private $brewery_slug;
    
    /**
     * @ORM\Column(type="string")
     */
    private $brewery_label;
    
    /**
     * @ORM\Column(type="string")
     */
    private $country_name;
    
    /**
     * @ORM\Column(type="boolean")
     */
    private $brewery_in_production;
    
    /**
     * @ORM\Column(type="boolean")
     */
    private $brewery_active;
    
    /**
     * @ORM\Column(type="boolean")
     */
    private $is_independent;
    
    /**
     * @ORM\OneToOne(targetEntity="ClaimedStatus")
     * @ORM\JoinColumn(name="claimed_status_id", referencedColumnName="id")
     */
    private $claimed_status;
    
    /**
     * @ORM\Column(type="integer")
     */
    private $beer_count;
    
    /**
     * @ORM\OneToOne(targetEntity="Contact")
     * @ORM\JoinColumn(name="contact_id", referencedColumnName="id")
     */
    private $contact;
    
    /**
     * @ORM\Column(type="string")
     */
    private $brewery_type;
    
    /**
     * @ORM\Column(type="integer")
     */
    private $brewery_type_id;
    
    /**
     * @ORM\OneToOne(targetEntity="Location")
     * @ORM\JoinColumn(name="location_id", referencedColumnName="id")
     */
    private $location;
    
    /**
     * @ORM\OneToOne(targetEntity="Rating")
     * @ORM\JoinColumn(name="rating_id", referencedColumnName="id")
     */
    private $rating;
    
    /**
     * @ORM\Column(type="string")
     */
    private $brewery_description;
    
    /**
     * @ORM\OneToOne(targetEntity="Stats")
     * @ORM\JoinColumn(name="stats_id", referencedColumnName="id")
     */
    private $stats;
    
    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Beer\Beer", mappedBy="brewery")
     */
    private $beers;
    
    /**
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(type="datetime", name="internal_created_at", nullable=true)
     */
    private $internal_created_at;
    
    /**
     * @Gedmo\Timestampable(on="update")
     * @ORM\Column(type="datetime", name="internal_updated_at", nullable=true)
     */
    private $internal_updated_at;
    
    /**
     * @ORM\Column(type="boolean", options={"default":false})
     */
    private $internal_data_gathered;
}
