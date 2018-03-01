<?php

namespace App\Entity\Checkin;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Table(name="checkin")
 * @ORM\Entity(repositoryClass="App\Repository\Checkin\CheckinRepository")
 */
class Checkin
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;
    
    /**
     * @ORM\Column(type="datetime")
     */
    private $created_at;
    
    /**
     * @ORM\Column(type="decimal")
     */
    private $rating_score;
    
    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User\User", inversedBy="checkins")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     */
    private $user;
    
    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Beer\Beer", inversedBy="checkins")
     * @ORM\JoinColumn(name="beer_id", referencedColumnName="id")
     */
    private $beer;
    
    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Venue\Venue", inversedBy="checkins")
     * @ORM\JoinColumn(name="venue_id", referencedColumnName="id", nullable=true)
     */
    private $venue;
    
    /**
     * @ORM\Column(type="integer")
     */
    private $total_comments;
    
    /**
     * @ORM\OneToMany(targetEntity="Comment", mappedBy="checkin")
     */
    private $comments;
    
    /**
     * @ORM\Column(type="integer")
     */
    private $total_toasts;
    
    /**
     * @ORM\OneToMany(targetEntity="Toast", mappedBy="checkin")
     */
    private $toasts;
    
    /**
     * @ORM\ManyToOne(targetEntity="Source", inversedBy="checkins")
     * @ORM\JoinColumn(name="source_id", referencedColumnName="id", nullable=true)
     */
    private $source;
    
    /**
     * @ORM\OneToMany(targetEntity="Media", mappedBy="checkin")
     */
    private $medias;
    
    /**
     * @ORM\Column(type="integer")
     */
    private $total_badges;
    
    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Badge\Badge", inversedBy="checkins")
     * @ORM\JoinTable(name="rel_checkin_badge")
     */
    private $badges;
    
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
}
