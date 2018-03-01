<?php

namespace App\Entity\Badge;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Table(name="badge")
 * @ORM\Entity(repositoryClass="App\Repository\Badge\BadgeRepository")
 */
class Badge
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
    private $badge_name;
    
    /**
     * @ORM\Column(type="text")
     */
    private $badge_description;
    
    /**
     * @ORM\Column(type="string")
     */
    private $badge_hint;
    
    /**
     * @ORM\Column(type="boolean")
     */
    private $badge_active_status;
        
    /**
     * @ORM\Column(type="integer")
     */
    private $category_id;
    
    /**
     * @ORM\OneToOne(targetEntity="Media")
     * @ORM\JoinColumn(name="media_id", referencedColumnName="id")
     */
    private $media;
    
    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Checkin\Checkin", mappedBy="groups")
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
}
