<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\EventRepository")
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
     * @ORM\ManyToMany(targetEntity="\App\Entity\Venue\Venue")
     * @ORM\JoinTable(name="event_venues",
     *      joinColumns={@ORM\JoinColumn(name="event_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="venue_id", referencedColumnName="id")}
     *      )
     */
    private $venues;
    
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
    
    
}
