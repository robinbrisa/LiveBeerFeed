<?php

namespace App\Entity\Event;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="event_taplist_queue")
 * @ORM\Entity(repositoryClass="App\Repository\Event\TapListQueueRepository")
 */
class TapListQueue
{    
    /**
     * @ORM\Id()
     * @ORM\Column(type="integer")
     */
    private $untappdID;
    
    /**
     * @ORM\Id()
     * @ORM\ManyToOne(targetEntity="Session", inversedBy="messages")
     * @ORM\JoinColumn(name="session_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $session;
    
     /**
     * Get $untappdID
     *
     * @return mixed
    */
    public function getUntappdID()
    {
        return $this->untappdID;
    }

     /**
     * Set $untappdID
     *
     * @param mixed $untappdID
     *
     * @return TapListQueue
    */
    public function setUntappdID($untappdID)
    {
        $this->untappdID = $untappdID;
        
        return $this;
    }
    
    /**
     * Get $session
     *
     * @return mixed
     */
    public function getSession()
    {
        return $this->session;
    }
    
    /**
     * Set $session
     *
     * @param mixed $event
     *
     * @return Session
     */
    public function setSession($session)
    {
        $this->session = $session;
        
        return $this;
    }
    
}
