<?php

namespace App\Entity\Event;

use App\Entity\Beer\LocalBeer;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Table(name="event_publisher", uniqueConstraints={@ORM\UniqueConstraint(name="publisher_by_event_unique", columns={"name", "event_id"})})
 * @ORM\Entity(repositoryClass="App\Repository\Event\PublisherRepository")
 */
class Publisher
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;
    
    /**
     * @ORM\Column(type="string", nullable=false)
     */
    private $name;
    
    /**
     * @ORM\Column(type="string", nullable=false, unique=true)
     */
    private $access_key;    
    
    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $last_publication_date;
    
    /**
     * @ORM\Column(type="integer", nullable=false)
     */
    private $remaining_messages = 3;
    
    /**
     * @ORM\ManyToOne(targetEntity="Event")
     * @ORM\JoinColumn(name="event_id", referencedColumnName="id", onDelete="CASCADE", nullable=false)
     */
    private $event;
    
    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $email;
    
    /**
     * @ORM\Column(type="string")
     */
    private $language = 'en';
    
    /**
     * @ORM\Column(type="boolean")
     */
    private $notified = false;
    
    /**
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(type="datetime", name="created_at", nullable=true)
     */
    private $created_at;
    
    /**
     * @Gedmo\Timestampable(on="update")
     * @ORM\Column(type="datetime", name="updated_at", nullable=true)
     */
    private $updated_at;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Event\TapListItem", mappedBy="owner")
     */
    private $tap_list_items;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Beer\LocalBeer", mappedBy="owner", orphanRemoval=true)
     */
    private $local_beers;
    
     /**
     * Get $email
     *
     * @return mixed
    */
    public function getEmail()
    {
        return $this->email;
    }

     /**
     * Set $email
     *
     * @param mixed $email
     *
     * @return Publisher
    */
    public function setEmail($email)
    {
        $this->email = $email;
        
        return $this;
    }

     /**
     * Get $language
     *
     * @return string
    */
    public function getLanguage()
    {
        return $this->language;
    }

     /**
     * Set $language
     *
     * @param string $language
     *
     * @return Publisher
    */
    public function setLanguage($language)
    {
        $this->language = $language;
        
        return $this;
    }

     /**
     * Get $notified
     *
     * @return mixed
    */
    public function getNotified()
    {
        return $this->notified;
    }

     /**
     * Set $notified
     *
     * @param mixed $notified
     *
     * @return Publisher
    */
    public function setNotified($notified)
    {
        $this->notified = $notified;
        
        return $this;
    }

    public function __toString()
    {
        return $this->name;
    }
    
    public function __construct() {
        $this->access_key = bin2hex(random_bytes(3));
        $this->tap_list_items = new ArrayCollection();
        $this->local_beers = new ArrayCollection();
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
     * @return Publisher
    */
    public function setName($name)
    {
        $this->name = $name;
        
        return $this;
    }

     /**
     * Get $access_key
     *
     * @return string
    */
    public function getAccessKey()
    {
        return $this->access_key;
    }

     /**
     * Set $access_key
     *
     * @param string $access_key
     *
     * @return Publisher
    */
    public function setAccessKey($access_key)
    {
        $this->access_key = $access_key;
        
        return $this;
    }

     /**
     * Get $last_publication_date
     *
     * @return mixed
    */
    public function getLastPublicationDate()
    {
        return $this->last_publication_date;
    }

     /**
     * Set $last_publication_date
     *
     * @param mixed $last_publication_date
     *
     * @return Publisher
    */
    public function setLastPublicationDate($last_publication_date)
    {
        $this->last_publication_date = $last_publication_date;
        
        return $this;
    }
    
    public function getMinutesSinceLastPublication() {
        if (!$this->last_publication_date) {
            return null;
        }
        $difference = $this->last_publication_date->diff(new \DateTime('now'));
        $minutes = $difference->days * 24 * 60;
        $minutes += $difference->h * 60;
        $minutes += $difference->i;
        return $minutes;
    }

     /**
     * Get $remaining_messages
     *
     * @return number
    */
    public function getRemainingMessages()
    {
        return $this->remaining_messages;
    }

     /**
     * Set $remaining_messages
     *
     * @param number $remaining_messages
     *
     * @return Publisher
    */
    public function setRemainingMessages($remaining_messages)
    {
        $this->remaining_messages = $remaining_messages;
        
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
     * @return Publisher
    */
    public function setEvent($event)
    {
        $this->event = $event;
        
        return $this;
    }

     /**
     * Get $created_at
     *
     * @return mixed
    */
    public function getCreatedAt()
    {
        return $this->created_at;
    }

     /**
     * Set $created_at
     *
     * @param mixed $created_at
     *
     * @return Publisher
    */
    public function setCreatedAt($created_at)
    {
        $this->created_at = $created_at;
        
        return $this;
    }

     /**
     * Get $updated_at
     *
     * @return mixed
    */
    public function getUpdatedAt()
    {
        return $this->updated_at;
    }

     /**
     * Set $updated_at
     *
     * @param mixed $updated_at
     *
     * @return Publisher
    */
    public function setUpdatedAt($updated_at)
    {
        $this->updated_at = $updated_at;
        
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
            $tapListItem->setOwner($this);
        }

        return $this;
    }

    public function removeTapListItem(TapListItem $tapListItem): self
    {
        if ($this->tap_list_items->contains($tapListItem)) {
            $this->tap_list_items->removeElement($tapListItem);
            // set the owning side to null (unless already changed)
            if ($tapListItem->getOwner() === $this) {
                $tapListItem->setOwner(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|LocalBeer[]
     */
    public function getLocalBeers(): Collection
    {
        return $this->local_beers;
    }

    public function addLocalBeer(LocalBeer $localBeer): self
    {
        if (!$this->local_beers->contains($localBeer)) {
            $this->local_beers[] = $localBeer;
            $localBeer->setOwner($this);
        }

        return $this;
    }

    public function removeLocalBeer(LocalBeer $localBeer): self
    {
        if ($this->local_beers->contains($localBeer)) {
            $this->local_beers->removeElement($localBeer);
            // set the owning side to null (unless already changed)
            if ($localBeer->getOwner() === $this) {
                $localBeer->setOwner(null);
            }
        }

        return $this;
    }

}
