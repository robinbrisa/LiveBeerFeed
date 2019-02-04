<?php

namespace App\Entity\Badge;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;

/**
 * @ORM\Entity(repositoryClass="App\Repository\Badge\BadgeRelationRepository")
 * @ORM\Table(name="badge_relation",indexes={@ORM\Index(name="search_by_checkin_idx", columns={"checkin_id"})})
 */
class BadgeRelation
{
    /**
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="\App\Entity\Badge\Badge", inversedBy="badge_relation", cascade={"persist"})
     * @ORM\JoinColumn(name="badge_id", referencedColumnName="id")
     */
    private $badge;
    
    /**
     * 
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="\App\Entity\User\User", inversedBy="badge_relation")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $user;
    
    /**
     * @ORM\ManyToOne(targetEntity="\App\Entity\Checkin\Checkin", inversedBy="badge_relation")
     * @ORM\JoinColumn(name="checkin_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $checkin;
    
    /**
     * @ORM\Column(type="datetime")
     */
    private $created_at;
    
    /**
     * @ORM\Column(type="integer")
     */
    private $user_badge_id;
    
    /**
     * Set badge
     *
     * @param \App\Entity\Badge\Badge $badge
     *
     * @return BadgeRelation
     */
    public function setBadge(\App\Entity\Badge\Badge $badge = null)
    {
        $this->badge = $badge;
        
        return $this;
    }
    
    /**
     * Get badge
     *
     * @return \App\Entity\Badge\Badge
     */
    public function getBadge()
    {
        return $this->badge;
    }
    
    /**
     * Set checkin
     *
     * @param \App\Entity\Checkin\Checkin $checkin
     *
     * @return BadgeRelation
     */
    public function setCheckin(\App\Entity\Checkin\Checkin $checkin = null)
    {
        $this->checkin = $checkin;
        
        return $this;
    }
    
    /**
     * Get checkin
     *
     * @return \App\Entity\Checkin\Checkin
     */
    public function getCheckin()
    {
        return $this->checkin;
    }
    
    /**
     * Set user
     *
     * @param \App\Entity\User\User $user
     *
     * @return BadgeRelation
     */
    public function setUser(\App\Entity\User\User $user = null)
    {
        $this->user = $user;
        
        return $this;
    }
    
    /**
     * Get checkin
     *
     * @return \App\Entity\User\User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set userBadgeId
     *
     * @param integer $userBadgeId
     *
     * @return BadgeRelation
     */
    public function setUserBadgeId($userBadgeId)
    {
        $this->user_badge_id = $userBadgeId;
        
        return $this;
    }
    
    /**
     * Get userBadgeId
     *
     * @return integer
     */
    public function getUserBadgeId()
    {
        return $this->user_badge_id;
    }
    
    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     *
     * @return BadgeRelation
     */
    public function setCreatedAt($createdAt)
    {
        $this->created_at = $createdAt;
        
        return $this;
    }
    
    /**
     * Get createdAt
     *
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->created_at;
    }
}
