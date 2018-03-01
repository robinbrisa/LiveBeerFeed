<?php

namespace App\Entity\Beer;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Table(name="beer")
 * @ORM\Entity(repositoryClass="App\Repository\Beer\BeerRepository")
 */
class Beer
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     */
    private $id;
    
    /**
     * @ORM\Column(type="string")
     */
    private $beer_name;
    
    /**
     * @ORM\Column(type="string")
     */
    private $beer_label;
    
    /**
     * @ORM\Column(type="decimal")
     */
    private $beer_abv;
    
    /**
     * @ORM\Column(type="decimal")
     */
    private $beer_ibu;
    
    /**
     * @ORM\Column(type="text")
     */
    private $beer_description;
    
    /**
     * @ORM\Column(type="string")
     */
    private $beer_style;
    
    /**
     * @ORM\Column(type="boolean")
     */
    private $is_in_production;
    
    /**
     * @ORM\Column(type="string")
     */
    private $beer_slug;
    
    /**
     * @ORM\Column(type="boolean")
     */
    private $in_homebrew;
    
    /**
     * @ORM\Column(type="datetime")
     */
    private $created_at;
    
    /**
     * @ORM\Column(type="integer")
     */
    private $rating_count;
    
    /**
     * @ORM\Column(type="integer")
     */
    private $rating_score;
    
    /**
     * @ORM\OneToOne(targetEntity="Stats")
     * @ORM\JoinColumn(name="stats_id", referencedColumnName="id")
     */
    private $stats;
    
    /**
     * @ORM\Column(type="integer")
     */
    private $total_count;
    
    /**
     * @ORM\Column(type="integer")
     */
    private $monthly_count;
    
    /**
     * @ORM\Column(type="integer")
     */
    private $total_user_count;
    
    /**
     * @ORM\Column(type="integer")
     */
    private $user_count;
    
    /**.
     * @ManyToOne(targetEntity="Brewery\Brewery", inversedBy="beers")
     * @JoinColumn(name="brewery_id", referencedColumnName="id")
     */
    private $brewery;
        
    /**
     * @ORM\OneToMany(targetEntity="Vintage", mappedBy="parent_id")
     */
    private $vintages;
    
    /**
     * @ORM\OneToMany(targetEntity="Vintage", mappedBy="vintage_id")
     */
    private $parent;
    
    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Checkin\Checkin", mappedBy="beer")
     */
    private $checkins;
    
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
