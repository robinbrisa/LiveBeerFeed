<?php

namespace App\Entity\Event;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
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
     * @ORM\OneToMany(targetEntity="App\Entity\Event\TapListItem", mappedBy="session", orphanRemoval=true)
     */
    private $tap_list_items;
    

    public function __construct() {
        $this->beers = new \Doctrine\Common\Collections\ArrayCollection();
        $this->out_of_stock = new \Doctrine\Common\Collections\ArrayCollection();
        $this->tap_list_items = new ArrayCollection();
    }
    
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
     * @return Collection|TapListItem[]
     */
    public function getTapListItems(): Collection
    {
        return $this->tap_list_items;
    }

    public function addTapListItem(TapListItem $tapListItem): self
    {
        if (!$this->tap_list_items->contains($tapListItem)) {
            $this->tap_list_items[] = $tapListItem;
            $tapListItem->setSession($this);
        }

        return $this;
    }

    public function removeTapListItem(TapListItem $tapListItem): self
    {
        if ($this->tap_list_items->contains($tapListItem)) {
            $this->tap_list_items->removeElement($tapListItem);
            // set the owning side to null (unless already changed)
            if ($tapListItem->getSession() === $this) {
                $tapListItem->setSession(null);
            }
        }

        return $this;
    }
    
}
