<?php

namespace App\Entity\Event;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\Event\EventRepository")
 */
class Event
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
    private $name;
    
    /**
     * @ORM\Column(type="datetime")
     */
    private $start_date;
    
    /**
     * @ORM\Column(type="datetime")
     */
    private $end_date;
    
    /**
     * @ORM\OneToOne(targetEntity="Style")
     * @ORM\JoinColumn(name="style_id", referencedColumnName="id", nullable=true)
     */
    private $style;
    
    /**
     * @ORM\OneToMany(targetEntity="Message", mappedBy="event", cascade={"persist"}, orphanRemoval=true)
     */
    private $messages;
    
    /**
     * @ORM\ManyToMany(targetEntity="\App\Entity\Venue\Venue")
     * @ORM\JoinTable(name="event_venues",
     *      joinColumns={@ORM\JoinColumn(name="event_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="venue_id", referencedColumnName="id")}
     *      )
     */
    private $venues;
    
    /**
     * @ORM\Column(type="boolean")
     */
    private $last_info_stats;
    
    /**
     * @ORM\Column(type="datetime")
     */
    private $last_info_polling;
    
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
     * Set name
     *
     * @param string $name
     *
     * @return Event
     */
    public function setName($name)
    {
        $this->name = $name;
        
        return $this;
    }
    
    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }
    
    /**
     * Set style
     *
     * @param \App\Entity\Event\Style $style
     *
     * @return Event
     */
    public function setStyle(\App\Entity\Event\Style $style = null)
    {
        $this->style = $style;
        
        return $this;
    }
    
    /**
     * Get style
     *
     * @return \App\Entity\Event\Style
     */
    public function getStyle()
    {
        return $this->style;
    }
    
    
    /**
     * Add venue
     *
     * @param \App\Entity\Venue\Venue $venue
     *
     * @return Event
     */
    public function addVenue(\App\Entity\Venue\Venue $venue)
    {
        $this->venues[] = $venue;
        
        return $this;
    }
    
    /**
     * Remove venue
     *
     * @param \App\Entity\Beer\Vintage $vintage
     */
    public function removeVenue(\App\Entity\Venue\Venue $venue)
    {
        $this->venues->removeElement($venue);
    }
    
    /**
     * Get venues
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getVenues()
    {
        return $this->venues;
    }
    
    /**
     * Set startDate
     *
     * @param \DateTime $startDate
     *
     * @return Event
     */
    public function setStartDate($startDate)
    {
        $this->start_date = $startDate;
        
        return $this;
    }
    
    /**
     * Get startDate
     *
     * @return \DateTime
     */
    public function getStartDate()
    {
        return $this->start_date;
    }
    
    /**
     * Set endDate
     *
     * @param \DateTime $endDate
     *
     * @return Event
     */
    public function setEndDate($endDate)
    {
        $this->end_date = $endDate;
        
        return $this;
    }
    
    /**
     * Get endDate
     *
     * @return \DateTime
     */
    public function getEndDate()
    {
        return $this->end_date;
    }
    
    /**
     * Add message
     *
     * @param \App\Entity\Event\Message $message
     *
     * @return Event
     */
    public function addMessage(\App\Entity\Event\Message $message)
    {
        $this->messages[] = $message;
        $message->setEvent($this);
        
        return $this;
    }
    
    /**
     * Remove message
     *
     * @param \App\Entity\Event\Message $message
     */
    public function removeMessage(\App\Entity\Event\Message $message)
    {
        $this->messages->removeElement($message);
    }
    
    /**
     * Get message
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getMessages()
    {
        return $this->messages;
    }
    
    /**
     * Set lastInfoStats
     *
     * @param boolean $lastInfoStats
     *
     * @return Event
     */
    public function setLastInfoStats($lastInfoStats)
    {
        $this->last_info_stats = $lastInfoStats;
        
        return $this;
    }
    
    /**
     * Get lastInfoStats
     *
     * @return boolean
     */
    public function getLastInfoStats()
    {
        return $this->last_info_stats;
    }
    
    /**
     * Set lastInfoPolling
     *
     * @param \Datetime $lastInfoPolling
     *
     * @return Event
     */
    public function setLastInfoPolling($lastInfoPolling)
    {
        $this->last_info_polling = $lastInfoPolling;
        
        return $this;
    }
    
    /**
     * Get lastInfoPolling
     *
     * @return \Datetime
     */
    public function getLastInfoPolling()
    {
        return $this->last_info_polling;
    }
}
