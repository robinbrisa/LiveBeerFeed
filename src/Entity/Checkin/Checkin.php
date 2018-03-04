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
     * @ORM\Column(type="integer")
     */
    private $id;
    
    /**
     * @ORM\Column(type="datetime")
     */
    private $created_at;
    
    /**
     * @ORM\Column(type="string")
     */
    private $comment;
    
    /**
     * @ORM\Column(type="decimal", scale=2)
     */
    private $rating_score;
    
    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User\User", inversedBy="checkins", cascade={"persist"})
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     */
    private $user;
    
    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Beer\Beer", inversedBy="checkins", cascade={"persist"})
     * @ORM\JoinColumn(name="beer_id", referencedColumnName="id")
     */
    private $beer;
    
    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Venue\Venue", inversedBy="checkins", cascade={"persist"})
     * @ORM\JoinColumn(name="venue_id", referencedColumnName="id", nullable=true)
     */
    private $venue;
    
    /**
     * @ORM\Column(type="integer")
     */
    private $total_comments;
    
    /**
     * @ORM\OneToMany(targetEntity="Comment", mappedBy="checkin", cascade={"persist"}, orphanRemoval=true)
     */
    private $comments;
    
    /**
     * @ORM\Column(type="integer")
     */
    private $total_toasts;
    
    /**
     * @ORM\OneToMany(targetEntity="Toast", mappedBy="checkin", cascade={"persist"}, orphanRemoval=true)
     */
    private $toasts;
    
    /**
     * @ORM\ManyToOne(targetEntity="Source", inversedBy="checkins", cascade={"persist"})
     * @ORM\JoinColumn(name="source_id", referencedColumnName="id", onDelete="SET NULL", nullable=true)
     */
    private $source;
    
    /**
     * @ORM\OneToMany(targetEntity="Media", mappedBy="checkin", cascade={"persist"}, orphanRemoval=true)
     */
    private $medias;
    
    /**
     * @ORM\Column(type="integer")
     */
    private $total_badges;
    
    /**
     * @ORM\OneToMany(targetEntity="\App\Entity\Badge\BadgeRelation", mappedBy="checkin")
     */
    private $badge_relation;
    
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
        $this->comments = new \Doctrine\Common\Collections\ArrayCollection();
        $this->toasts = new \Doctrine\Common\Collections\ArrayCollection();
        $this->medias = new \Doctrine\Common\Collections\ArrayCollection();
        $this->badges = new \Doctrine\Common\Collections\ArrayCollection();
    }
    
    public function __toString()
    {
        return "Check-in of " . $this->beer->getName() . " by " . $this->user->getUserName() . ".";
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
     * Set id
     *
     * @param integer $id
     *
     * @return Checkin
     */
    public function setId($id)
    {
        $this->id = $id;
        
        return $this;
    }
    
    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     *
     * @return Checkin
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

    /**
     * Set ratingScore
     *
     * @param string $ratingScore
     *
     * @return Checkin
     */
    public function setRatingScore($ratingScore)
    {
        $this->rating_score = $ratingScore;

        return $this;
    }

    /**
     * Get ratingScore
     *
     * @return string
     */
    public function getRatingScore()
    {
        return $this->rating_score;
    }
    
    /**
     * Set comment
     *
     * @param string $comment
     *
     * @return Checkin
     */
    public function setComment($comment)
    {
        $this->comment = $comment;
        
        return $this;
    }
    
    /**
     * Get comment
     *
     * @return string
     */
    public function getComment()
    {
        return $this->comment;
    }
    
    /**
     * Set totalComments
     *
     * @param integer $totalComments
     *
     * @return Checkin
     */
    public function setTotalComments($totalComments)
    {
        $this->total_comments = $totalComments;

        return $this;
    }

    /**
     * Get totalComments
     *
     * @return integer
     */
    public function getTotalComments()
    {
        return $this->total_comments;
    }

    /**
     * Set totalToasts
     *
     * @param integer $totalToasts
     *
     * @return Checkin
     */
    public function setTotalToasts($totalToasts)
    {
        $this->total_toasts = $totalToasts;

        return $this;
    }

    /**
     * Get totalToasts
     *
     * @return integer
     */
    public function getTotalToasts()
    {
        return $this->total_toasts;
    }

    /**
     * Set totalBadges
     *
     * @param integer $totalBadges
     *
     * @return Checkin
     */
    public function setTotalBadges($totalBadges)
    {
        $this->total_badges = $totalBadges;

        return $this;
    }

    /**
     * Get totalBadges
     *
     * @return integer
     */
    public function getTotalBadges()
    {
        return $this->total_badges;
    }

    /**
     * Set internalCreatedAt
     *
     * @param \DateTime $internalCreatedAt
     *
     * @return Checkin
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
     * @return Checkin
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
     * Set user
     *
     * @param \App\Entity\User\User $user
     *
     * @return Checkin
     */
    public function setUser(\App\Entity\User\User $user = null)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return \App\Entity\User\User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set beer
     *
     * @param \App\Entity\Beer\Beer $beer
     *
     * @return Checkin
     */
    public function setBeer(\App\Entity\Beer\Beer $beer = null)
    {
        $this->beer = $beer;

        return $this;
    }

    /**
     * Get beer
     *
     * @return \App\Entity\Beer\Beer
     */
    public function getBeer()
    {
        return $this->beer;
    }

    /**
     * Set venue
     *
     * @param \App\Entity\Venue\Venue $venue
     *
     * @return Checkin
     */
    public function setVenue(\App\Entity\Venue\Venue $venue = null)
    {
        $this->venue = $venue;

        return $this;
    }

    /**
     * Get venue
     *
     * @return \App\Entity\Venue\Venue
     */
    public function getVenue()
    {
        return $this->venue;
    }

    /**
     * Add comment
     *
     * @param \App\Entity\Checkin\Comment $comment
     *
     * @return Checkin
     */
    public function addComment(\App\Entity\Checkin\Comment $comment)
    {
        $this->comments[] = $comment;
        $comment->setCheckin($this);
        
        return $this;
    }

    /**
     * Remove comment
     *
     * @param \App\Entity\Checkin\Comment $comment
     */
    public function removeComment(\App\Entity\Checkin\Comment $comment)
    {
        $this->comments->removeElement($comment);
    }

    /**
     * Get comments
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getComments()
    {
        return $this->comments;
    }
    
    /**
     * Reset comments
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function resetComments()
    {
        $this->comments = new \Doctrine\Common\Collections\ArrayCollection();
        
        return $this->comments;
    }
    
    /**
     * Add toast
     *
     * @param \App\Entity\Checkin\Toast $toast
     *
     * @return Checkin
     */
    public function addToast(\App\Entity\Checkin\Toast $toast)
    {
        $this->toasts[] = $toast;
        $toast->setCheckin($this);
        
        return $this;
    }

    /**
     * Remove toast
     *
     * @param \App\Entity\Checkin\Toast $toast
     */
    public function removeToast(\App\Entity\Checkin\Toast $toast)
    {
        $this->toasts->removeElement($toast);
    }

    /**
     * Get toasts
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getToasts()
    {
        return $this->toasts;
    }
    
    /**
     * Reset toasts
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function resetToasts()
    {
        $this->toasts = new \Doctrine\Common\Collections\ArrayCollection();
        
        return $this->toasts;
    }
    
    /**
     * Set source
     *
     * @param \App\Entity\Checkin\Source $source
     *
     * @return Checkin
     */
    public function setSource(\App\Entity\Checkin\Source $source = null)
    {
        $this->source = $source;

        return $this;
    }

    /**
     * Get source
     *
     * @return \App\Entity\Checkin\Source
     */
    public function getSource()
    {
        return $this->source;
    }

    /**
     * Add media
     *
     * @param \App\Entity\Checkin\Media $media
     *
     * @return Checkin
     */
    public function addMedia(\App\Entity\Checkin\Media $media)
    {
        $this->medias[] = $media;
        $media->setCheckin($this);

        return $this;
    }

    /**
     * Remove media
     *
     * @param \App\Entity\Checkin\Media $media
     */
    public function removeMedia(\App\Entity\Checkin\Media $media)
    {
        $this->medias->removeElement($media);
    }

    /**
     * Get medias
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getMedias()
    {
        return $this->medias;
    }
    
    /**
     * Reset medias
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function resetMedias()
    {
        $this->medias = new \Doctrine\Common\Collections\ArrayCollection();
        
        return $this->medias;
    }
    
    /**
     * Add badgeRelation
     *
     * @param \App\Entity\Badge\BadgeRelation $badgeRelation
     *
     * @return Checkin
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
