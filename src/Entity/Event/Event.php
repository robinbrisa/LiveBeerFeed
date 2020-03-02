<?php

namespace App\Entity\Event;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

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
     * @ORM\Column(type="string", nullable=true, unique=true)
     */
    private $slug;
    
    /**
     * @ORM\Column(type="datetime", nullable=false)
     */
    private $start_date;
    
    /**
     * @ORM\Column(type="datetime", nullable=false)
     */
    private $end_date;
    
    /**
     * @ORM\ManyToOne(targetEntity="Style")
     * @ORM\JoinColumn(name="style_id", referencedColumnName="id", nullable=true, onDelete="SET NULL")
     */
    private $style;
    
    /**
     * @ORM\OneToMany(targetEntity="Message", mappedBy="event", cascade={"persist"})
     */
    private $messages;
    
    /**
     * @ORM\OneToMany(targetEntity="Session", mappedBy="event", cascade={"persist"})
     */
    private $sessions;
    
    /**
     * @ORM\ManyToMany(targetEntity="\App\Entity\Venue\Venue")
     * @ORM\JoinTable(name="event_venues",
     *      joinColumns={@ORM\JoinColumn(name="event_id", referencedColumnName="id", onDelete="CASCADE")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="venue_id", referencedColumnName="id")}
     *      )
     */
    private $venues;
    
    /**
     * @ORM\Column(type="boolean")
     */
    private $last_info_stats = 0;
    
    /**
     * @ORM\Column(type="datetime")
     */
    private $last_info_polling;
    
    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $event_logo;
    
    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $event_logo_notification;
    
    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $locale = 'en';
    
    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $screen_size = 'normal';
    
    /**
     * @ORM\Column(type="boolean")
     */
    private $moderated = false;
    
    /**
     * @ORM\Column(type="boolean")
     */
    private $has_taplist = false;
    
    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\User\User", mappedBy="attending")
     */
    private $users_attending;
    
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
     * @ORM\Column(type="boolean")
     */
    private $hidden = 0;

    /**
     * @ORM\Column(type="boolean")
     */
    private $allowLocalBeer = 0;

    /**
     * @ORM\Column(type="boolean")
     */
    private $lengthSort = 0;

      /**
     * Get $has_taplist
     *
     * @return boolean
    */
    public function getHasTaplist()
    {
        return $this->has_taplist;
    }

     /**
     * Set $has_taplist
     *
     * @param boolean $has_taplist
     *
     * @return Event
    */
    public function setHasTaplist($has_taplist)
    {
        $this->has_taplist = $has_taplist;
        
        return $this;
    }

    /**
     * Get $moderated
     *
     * @return boolean
    */
    public function getModerated()
    {
        return $this->moderated;
    }

     /**
     * Set $moderated
     *
     * @param boolean $moderated
     *
     * @return Event
    */
    public function setModerated($moderated)
    {
        $this->moderated = $moderated;
        
        return $this;
    }

    public function __toString()
    {
        return $this->name;
    }
    
    public function __construct() {
        $this->last_info_polling = new \DateTime();
        $this->users_attending = new \Doctrine\Common\Collections\ArrayCollection();
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
     * Set slug
     *
     * @param string $slug
     *
     * @return Event
     */
    public function setSlug($slug)
    {
        $this->slug = $slug;
        
        return $this;
    }
    
    /**
     * Get slug
     *
     * @return string
     */
    public function getSlug()
    {
        return $this->slug;
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
     * Add session
     *
     * @param \App\Entity\Event\Message $message
     *
     * @return Event
     */
    public function addSession(\App\Entity\Event\Session $session)
    {
        $this->sessions[] = $session;
        $session->setEvent($this);
        
        return $this;
    }
    
    /**
     * Remove session
     *
     * @param \App\Entity\Event\Session $session
     */
    public function removeSession(\App\Entity\Event\Session $session)
    {
        $this->messages->removeElement($session);
    }
    
    /**
     * Get session
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getSessions()
    {
        return $this->sessions;
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
    
    /**
     * Get createdAt
     *
     * @return \Datetime
     */
    public function getCreatedAt()
    {
        return $this->created_at;
    }
    
    /**
     * Get updatedAt
     *
     * @return \Datetime
     */
    public function getUpdatedAt()
    {
        return $this->updated_at;
    }
    
    /**
     * @return mixed
     */
    public function getEventLogo()
    {
        return $this->event_logo;
    }
    
    /**
     * @param mixed $event_logo
     */
    public function setEventLogo($event_logo)
    {
        $this->event_logo = $event_logo;
    }
    
    /**
     * @return mixed
     */
    public function getEventLogoNotification()
    {
        return $this->event_logo_notification;
    }
    
    /**
     * @param mixed $event_logo_notification
     */
    public function setEventLogoNotification($event_logo_notification)
    {
        $this->event_logo_notification = $event_logo_notification;
    }
    
    /**
     * @return mixed
     */
    public function getLocale()
    {
        return $this->locale;
    }
    
    /**
     * @param mixed $locale
     */
    public function setLocale($locale)
    {
        $this->locale = $locale;
    }
    
    /**
     * @return mixed
     */
    public function getScreenSize()
    {
        return $this->screen_size;
    }
    
    /**
     * @param mixed $locale
     */
    public function setScreenSize($screenSize)
    {
        $this->screen_size = $screenSize;
    }
    
    /**
     * Add user_attending
     *
     * @param \App\Entity\User\User $event
     *
     * @return Event
     */
    public function addUserAttending(\App\Entity\User\User $user)
    {
        $this->users_attending[] = $user;
        
        return $this;
    }
    
    /**
     * Remove user_attending
     *
     * @param \App\Entity\Event\Event $event
     */
    public function removeUserAttending(\App\Entity\User\User $user)
    {
        $this->users_attending->removeElement($user);
    }
    
    /**
     * Get users_attending
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getUsersAttending()
    {
        return $this->users_attending;
    }

    public function getHidden(): ?bool
    {
        return $this->hidden;
    }

    public function setHidden(bool $hidden): self
    {
        $this->hidden = $hidden;

        return $this;
    }

    public function getAllowLocalBeer(): ?bool
    {
        return $this->allowLocalBeer;
    }

    public function setAllowLocalBeer(bool $allowLocalBeer): self
    {
        $this->allowLocalBeer = $allowLocalBeer;

        return $this;
    }

    public function getLengthSort(): ?bool
    {
        return $this->lengthSort;
    }

    public function setLengthSort(bool $lengthSort): self
    {
        $this->lengthSort = $lengthSort;

        return $this;
    }
}
