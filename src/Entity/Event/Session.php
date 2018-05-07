<?php

namespace App\Entity\Event;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(name="event_session")
 * @ORM\Entity(repositoryClass="App\Repository\Event\SessionRepository")
 */
class Session
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;
    
    /**
     * @Assert\NotBlank()
     * @Assert\Length(max = 100, maxMessage = "Session name is too long")
     * @ORM\Column(type="string")
     */
    private $name;
    
    /**
     * @ORM\Column(type="datetime", nullable=false)
     */
    private $start_date;
    
    /**
     * @ORM\Column(type="datetime", nullable=false)
     */
    private $end_date;
    
    /**
     * @ORM\ManyToOne(targetEntity="Event", inversedBy="sessions")
     * @ORM\JoinColumn(name="event_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $event;
    
    /**
     * @ORM\Column(type="string", nullable=false)
     */
    private $color = '#000000';
    
    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Beer\Beer")
     * @ORM\JoinTable(name="event_session_taplist",
     *      joinColumns={@ORM\JoinColumn(name="session_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="beer_id", referencedColumnName="id")}
     *      )
     */
    private $beers;
    
     /**
     * Get $color
     *
     * @return string
    */
    public function getColor()
    {
        return $this->color;
    }

     /**
     * Set $color
     *
     * @param string $color
     *
     * @return Session
    */
    public function setColor($color)
    {
        $this->color = $color;
        
        return $this;
    }

    public function __toString() {
        return $this->name;
    }
    
    public function getId()
    {
        return $this->id;
    }
    
     /**
     * Get $name
     *
     * @return mixed
    */
    public function getName()
    {
        return $this->name;
    }
    
    /**
     * Set $name
     *
     * @param mixed $name
     *
     * @return Session
     */
    public function setName($name)
    {
        $this->name = $name;
        
        return $this;
    }

     /**
     * Get $start_date
     *
     * @return mixed
    */
    public function getStartDate()
    {
        return $this->start_date;
    }
    
    /**
     * Set $start_date
     *
     * @param mixed $start_date
     *
     * @return Session
     */
    public function setStartDate($start_date)
    {
        $this->start_date = $start_date;
        
        return $this;
    }
    
     /**
     * Get $end_date
     *
     * @return mixed
    */
    public function getEndDate()
    {
        return $this->end_date;
    }
    
    /**
     * Set $end_date
     *
     * @param mixed $end_date
     *
     * @return Session
     */
    public function setEndDate($end_date)
    {
        $this->end_date = $end_date;
        
        return $this;
    }

    /**
     * Get $event
     *
     * @return mixed
     */
    public function getEvent()
    {
        return $this->event;
    }
    
    /**
     * Set $event
     *
     * @param mixed $event
     *
     * @return Session
     */
    public function setEvent($event)
    {
        $this->event = $event;
        
        return $this;
    }
    
    
    /**
     * Add beer
     *
     * @param \App\Entity\Beer\Beer $beer
     *
     * @return Event
     */
    public function addBeer(\App\Entity\Beer\Beer $beer)
    {
        $this->beers[] = $beer;
        
        return $this;
    }
    
    /**
     * Remove beer
     *
     * @param \App\Entity\Beer\Vintage $vintage
     */
    public function removeBeer(\App\Entity\Beer\Beer $beer)
    {
        $this->beers->removeElement($beer);
    }
    
    /**
     * Get beers
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getBeers()
    {
        return $this->beers;
    }
    
    
}
