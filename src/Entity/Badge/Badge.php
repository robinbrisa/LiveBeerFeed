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
     * @ORM\Column(type="string", nullable=true)
     */
    private $badge_hint;
    
    /**
     * @ORM\Column(type="boolean")
     */
    private $badge_active_status = true;
        
    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $category_id;
    
    /**
     * @ORM\Column(type="string")
     */
    private $badge_image_sm;
    
    /**
     * @ORM\Column(type="string")
     */
    private $badge_image_md;
    
    /**
     * @ORM\Column(type="string")
     */
    private $badge_image_lg;
    
    /**
     * @ORM\OneToMany(targetEntity="BadgeRelation", mappedBy="badge")
     */
    private $badge_relation;
    
    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\User\User", mappedBy="badges")
     */
    private $users;
    
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
     * Constructor
     */
    public function __construct()
    {
        $this->checkins = new \Doctrine\Common\Collections\ArrayCollection();
    }
    
    /**
     * Set id
     *
     * @param integer $id
     *
     * @return Badge
     */
    public function setId($id)
    {
        $this->id = $id;
        
        return $this;
    }
    
    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set badgeName
     *
     * @param string $badgeName
     *
     * @return Badge
     */
    public function setBadgeName($badgeName)
    {
        $this->badge_name = $badgeName;

        return $this;
    }

    /**
     * Get badgeName
     *
     * @return string
     */
    public function getBadgeName()
    {
        return $this->badge_name;
    }

    /**
     * Set badgeDescription
     *
     * @param string $badgeDescription
     *
     * @return Badge
     */
    public function setBadgeDescription($badgeDescription)
    {
        $this->badge_description = $badgeDescription;

        return $this;
    }

    /**
     * Get badgeDescription
     *
     * @return string
     */
    public function getBadgeDescription()
    {
        return $this->badge_description;
    }

    /**
     * Set badgeHint
     *
     * @param string $badgeHint
     *
     * @return Badge
     */
    public function setBadgeHint($badgeHint)
    {
        $this->badge_hint = $badgeHint;

        return $this;
    }

    /**
     * Get badgeHint
     *
     * @return string
     */
    public function getBadgeHint()
    {
        return $this->badge_hint;
    }

    /**
     * Set badgeActiveStatus
     *
     * @param boolean $badgeActiveStatus
     *
     * @return Badge
     */
    public function setBadgeActiveStatus($badgeActiveStatus)
    {
        $this->badge_active_status = $badgeActiveStatus;

        return $this;
    }

    /**
     * Get badgeActiveStatus
     *
     * @return boolean
     */
    public function getBadgeActiveStatus()
    {
        return $this->badge_active_status;
    }

    /**
     * Set categoryId
     *
     * @param integer $categoryId
     *
     * @return Badge
     */
    public function setCategoryId($categoryId)
    {
        $this->category_id = $categoryId;

        return $this;
    }

    /**
     * Get categoryId
     *
     * @return integer
     */
    public function getCategoryId()
    {
        return $this->category_id;
    }

    /**
     * Set internalCreatedAt
     *
     * @param \DateTime $internalCreatedAt
     *
     * @return Badge
     */
    public function setInternalCreatedAt($internalCreatedAt)
    {
        $this->internal_created_at = $internalCreatedAt;

        return $this;
    }

    /**
     * Get internalCreatedAt
     *
     * @return \DateTime
     */
    public function getInternalCreatedAt()
    {
        return $this->internal_created_at;
    }

    /**
     * Set internalUpdatedAt
     *
     * @param \DateTime $internalUpdatedAt
     *
     * @return Badge
     */
    public function setInternalUpdatedAt($internalUpdatedAt)
    {
        $this->internal_updated_at = $internalUpdatedAt;

        return $this;
    }

    /**
     * Get internalUpdatedAt
     *
     * @return \DateTime
     */
    public function getInternalUpdatedAt()
    {
        return $this->internal_updated_at;
    }
    
    /**
     * Set badgeImageSm
     *
     * @param string $badgeImageSm
     *
     * @return Badge
     */
    public function setBadgeImageSm($badgeImageSm)
    {
        $this->badge_image_sm = $badgeImageSm;
        
        return $this;
    }
    
    /**
     * Get badgeImageSm
     *
     * @return string
     */
    public function getBadgeImageSm()
    {
        return $this->badge_image_sm;
    }
    
    /**
     * Set badgeImageMd
     *
     * @param string $badgeImageMd
     *
     * @return Badge
     */
    public function setBadgeImageMd($badgeImageMd)
    {
        $this->badge_image_md = $badgeImageMd;
        
        return $this;
    }
    
    /**
     * Get badgeImageMd
     *
     * @return string
     */
    public function getBadgeImageMd()
    {
        return $this->badge_image_md;
    }
    
    /**
     * Set badgeImageLg
     *
     * @param string $badgeImageLg
     *
     * @return Badge
     */
    public function setBadgeImageLg($badgeImageLg)
    {
        $this->badge_image_lg = $badgeImageLg;
        
        return $this;
    }
    
    /**
     * Get badgeImageLg
     *
     * @return string
     */
    public function getBadgeImageLg()
    {
        return $this->badge_image_lg;
    }
    
    /**
     * Add badgeRelation
     *
     * @param \App\Entity\Badge\BadgeRelation $badgeRelation
     *
     * @return Badge
     */
    public function addBadgeRelation(\App\Entity\Badge\BadgeRelation $badgeRelation)
    {
        $this->badge_relation[] = $badgeRelation;
        
        return $this;
    }
    
    /**
     * Remove badgeRelation
     *
     * @param \App\Entity\Badge\BadgeRelation $badgeRelation
     */
    public function removeBadgeRelation(\App\Entity\Badge\Badge $badgeRelation)
    {
        $this->badge_relation->removeElement($badgeRelation);
    }
    
    /**
     * Get badgeRelation
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getBadgeRelations()
    {
        return $this->badge_relation;
    }
}
