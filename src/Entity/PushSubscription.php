<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Entity(repositoryClass="App\Repository\PushSubscriptionRepository")
 * @ORM\Table(indexes={@ORM\Index(name="search_idx", columns={"endpoint", "event_id"})})
 */
class PushSubscription
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", nullable=false, unique=true)
     */
    private $endpoint;
    
    /**
     * @ORM\Column(type="string")
     */
    private $public_key;
    
    /**
     * @ORM\Column(type="string")
     */
    private $auth_token;
    
    /**
     * @ORM\Column(type="string")
     */
    private $content_encoding;
    
    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Event\Event")
     * @ORM\JoinColumn(name="event_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $event;
    
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
    
    
    public function getId()
    {
        return $this->id;
    }
    
    /**
     * Set event
     *
     * @param \App\Entity\Event\Event $event
     *
     * @return PushSubscription
     */
    public function setEvent(\App\Entity\Event\Event $event = null)
    {
        $this->event = $event;
        
        return $this;
    }
    
    /**
     * Get event
     *
     * @return \App\Entity\Event\Event
     */
    public function getEvent()
    {
        return $this->event;
    }
    
    /**
     * @return mixed
     */
    public function getEndpoint()
    {
        return $this->endpoint;
    }

    /**
     * @param mixed $endpoint
     */
    public function setEndpoint($endpoint)
    {
        $this->endpoint = $endpoint;
    }

    /**
     * @return mixed
     */
    public function getPublicKey()
    {
        return $this->public_key;
    }

    /**
     * @param mixed $public_key
     */
    public function setPublicKey($public_key)
    {
        $this->public_key = $public_key;
    }

    /**
     * @return mixed
     */
    public function getAuthToken()
    {
        return $this->auth_token;
    }

    /**
     * @param mixed $auth_token
     */
    public function setAuthToken($auth_token)
    {
        $this->auth_token = $auth_token;
    }

    /**
     * @return mixed
     */
    public function getContentEncoding()
    {
        return $this->content_encoding;
    }

    /**
     * @param mixed $content_encoding
     */
    public function setContentEncoding($content_encoding)
    {
        $this->content_encoding = $content_encoding;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     *
     * @return PushSubscription
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
     * Set updatedAt
     *
     * @param \DateTime $internalUpdatedAt
     *
     * @return PushSubscription
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updated_at = $updatedAt;
        
        return $this;
    }
    
    /**
     * Get updatedAt
     *
     * @return \DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updated_at;
    }
    
}
