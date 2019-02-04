<?php

namespace App\Entity\Venue;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;

/**
 * @ORM\Table(name="venue_category")
 * @ORM\Entity(repositoryClass="App\Repository\Venue\CategoryRepository")
 */
class Category
{
    /**
     * @ORM\Id
     * @ORM\Column(type="string")
     */
    private $id;
    
    /**
     * @ORM\Column(type="string")
     */
    private $name;
    
    /**
     * @ORM\Column(type="boolean")
     */
    private $is_primary;
    
    /**
     * @ORM\ManyToMany(targetEntity="Venue", mappedBy="categories")
     * @JMS\Exclude()
     */
    private $venues;
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->venues = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Set id
     *
     * @param string $id
     *
     * @return Category
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Get id
     *
     * @return string
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
     * @return Category
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
     * Set isPrimary
     *
     * @param boolean $isPrimary
     *
     * @return Category
     */
    public function setIsPrimary($isPrimary)
    {
        $this->is_primary = $isPrimary;

        return $this;
    }

    /**
     * Get isPrimary
     *
     * @return boolean
     */
    public function getIsPrimary()
    {
        return $this->is_primary;
    }

    /**
     * Add venue
     *
     * @param \App\Entity\Venue\Venue $venue
     *
     * @return Category
     */
    public function addVenue(\App\Entity\Venue\Venue $venue)
    {
        $this->venues[] = $venue;

        return $this;
    }

    /**
     * Remove venue
     *
     * @param \App\Entity\Venue\Venue $venue
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
}
