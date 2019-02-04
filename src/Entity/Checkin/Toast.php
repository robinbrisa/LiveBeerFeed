<?php

namespace App\Entity\Checkin;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;


/**
 * @ORM\Table(name="checkin_toast")
 * @ORM\Entity(repositoryClass="App\Repository\Checkin\ToastRepository")
 */
class Toast
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     */
    private $id;
    
    /**
     * @ORM\ManyToOne(targetEntity="Checkin", inversedBy="toasts", cascade={"remove"})
     * @ORM\JoinColumn(name="checkin_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $checkin;
    
    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User\User", inversedBy="toasts", cascade={"persist"})
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     */
    private $user;
        
    /**
     * @ORM\Column(type="datetime")
     */
    private $created_at;

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
     * @return Toast
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
     * @return Toast
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
     * Set checkin
     *
     * @param \App\Entity\Checkin\Checkin $checkin
     *
     * @return Toast
     */
    public function setCheckin(\App\Entity\Checkin\Checkin $checkin = null)
    {
        $this->checkin = $checkin;

        return $this;
    }

    /**
     * Get checkin
     *
     * @return \App\Entity\Checkin\Checkin
     */
    public function getCheckin()
    {
        return $this->checkin;
    }

    /**
     * Set user
     *
     * @param \App\Entity\User\User $user
     *
     * @return Toast
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
}
