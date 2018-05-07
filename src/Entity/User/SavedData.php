<?php

namespace App\Entity\User;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Table(name="user_saved_data")
 * @ORM\Entity(repositoryClass="App\Repository\User\SavedDataRepository")
 */
class SavedData
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;
    
    /**
     * @ORM\ManyToOne(targetEntity="User", inversedBy="saved_data")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     */
    private $user;
    
    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Event\Event")
     * @ORM\JoinColumn(name="event_id", referencedColumnName="id")
     */
    private $event;
    
    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $ticks;
    
    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $favorites;
    
    
    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $button_action;
    
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
     * Get $button_action
     *
     * @return mixed
    */
    public function getButtonAction()
    {
        return $this->button_action;
    }

     /**
     * Set $button_action
     *
     * @param mixed $button_action
     *
     * @return SavedData
    */
    public function setButtonAction($button_action)
    {
        $this->button_action = $button_action;
        
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
     * @return SavedData
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
     * @return SavedData
    */
    public function setUpdatedAt($updated_at)
    {
        $this->updated_at = $updated_at;
        
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
     * @return SavedData
    */
    public function setEvent($event)
    {
        $this->event = $event;
        
        return $this;
    }

     /**
     * Get $ticks
     *
     * @return mixed
    */
    public function getTicks()
    {
        return $this->ticks;
    }

     /**
     * Set $ticks
     *
     * @param mixed $ticks
     *
     * @return SavedData
    */
    public function setTicks($ticks)
    {
        $this->ticks = $ticks;
        
        return $this;
    }

     /**
     * Get $favorites
     *
     * @return mixed
    */
    public function getFavorites()
    {
        return $this->favorites;
    }

     /**
     * Set $favorites
     *
     * @param mixed $favorites
     *
     * @return SavedData
    */
    public function setFavorites($favorites)
    {
        $this->favorites = $favorites;
        
        return $this;
    }

    /**
     * Get $user
     *
     * @return mixed
    */
    public function getUser()
    {
        return $this->user;
    }

     /**
     * Set $user
     *
     * @param mixed $user
     *
     * @return SavedData
    */
    public function setUser($user)
    {
        $this->user = $user;
        
        return $this;
    }

    public function getId()
    {
        return $this->id;
    }
}
