<?php

namespace App\Entity\Beer;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="beer_style")
 * @ORM\Entity(repositoryClass="App\Repository\Beer\StyleRepository")
 */
class Style
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
     * @ORM\OneToMany(targetEntity="Beer", mappedBy="style")
     */
    private $beers;
    // ...
    
    public function __construct() {
        $this->beers = new \Doctrine\Common\Collections\ArrayCollection();
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
     * @return Style
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
    
}
