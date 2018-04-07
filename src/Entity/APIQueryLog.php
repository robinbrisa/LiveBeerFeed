<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="api_query_log")
 * @ORM\Entity(repositoryClass="App\Repository\APIQueryLogRepository")
 */
class APIQueryLog
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;
    
    /**
     * @ORM\Column(type="string")
     */
    private $method;
    
    /**
     * @ORM\Column(type="datetime")
     */
    private $date;
    
    /**
     * @ORM\Column(type="integer")
     */
    private $remaining_queries;
    
    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User\User")
     * @ORM\JoinColumn(name="user_key_id", referencedColumnName="id", onDelete="SET NULL")
     */
    private $user;
    
    public function getId()
    {
        return $this->id;
    }
    
    /**
     * Set method
     *
     * @param String $method
     *
     * @return APIQueryLog
     */
    public function setMethod($method)
    {
        $this->method = $method;
        
        return $this;
    }
    
    /**
     * Get method
     *
     * @return String
     */
    public function getMethod()
    {
        return $this->method;
    }
    
    /**
     * Set remainingQueries
     *
     * @param Integer $remainingQueries
     *
     * @return APIQueryLog
     */
    public function setRemainingQueries($remainingQueries)
    {
        $this->remaining_queries = $remainingQueries;
        
        return $this;
    }
    
    /**
     * Get remainingQueries
     *
     * @return Integer
     */
    public function getRemainingQueries()
    {
        return $this->remaining_queries;
    }
    
    /**
     * Set date
     *
     * @param \DateTime $date
     *
     * @return APIQueryLog
     */
    public function setDate($date)
    {
        $this->date = $date;
        
        return $this;
    }
    
    /**
     * Get date
     *
     * @return \DateTime
     */
    public function getDate()
    {
        return $this->date;
    }
    
    /**
     * Set user
     *
     * @param \App\Entity\User\User $user
     *
     * @return APIQueryLog
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
