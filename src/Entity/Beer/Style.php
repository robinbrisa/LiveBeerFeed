<?php

namespace App\Entity\Beer;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;

/**
 * @ORM\Table(name="beer_style", indexes={@ORM\Index(name="name_idx", columns={"name"})})
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
     * @ORM\Column(type="string", nullable=true)
     */
    private $color;
    
    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $category;
    
    /**
     * @ORM\OneToMany(targetEntity="Beer", mappedBy="style")
     * @JMS\Exclude()
     */
    private $beers;
    
     /**
     * Get $category
     *
     * @return mixed
    */
    public function getCategory()
    {
        return $this->category;
    }

     /**
     * Set $category
     *
     * @param mixed $category
     *
     * @return Style
    */
    public function setCategory($category)
    {
        $this->category = $category;
        
        return $this;
    }

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
    
    /**
     * Set color
     *
     * @param string $color
     *
     * @return Style
     */
    public function setColor($color)
    {
        $this->color = $color;
        
        return $this;
    }
    
    /**
     * Get color
     *
     * @return string
     */
    public function getColor()
    {
        return $this->color;
    }
    
}
