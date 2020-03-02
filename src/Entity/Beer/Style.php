<?php

namespace App\Entity\Beer;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
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
     * @ORM\ManyToMany(targetEntity="App\Entity\Beer\Category", inversedBy="styles")
     * @ORM\JoinTable(name="rel_style_category")
     */
    private $categories;
    
    public function __toString()
    {
        return $this->name;
    }
    
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
        $this->categories = new ArrayCollection();
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

    /**
     * @return Collection|Category[]
     */
    public function getCategories(): Collection
    {
        return $this->categories;
    }

    public function addCategory(Category $category): self
    {
        if (!$this->categories->contains($category)) {
            $this->categories[] = $category;
        }

        return $this;
    }

    public function removeCategory(Category $category): self
    {
        if ($this->categories->contains($category)) {
            $this->categories->removeElement($category);
        }

        return $this;
    }
    
    /**
     * @return Collection|Beer[]
     */
    public function getBeers(): Collection
    {
        return $this->beers;
    }
    
    public function addBeer(Beer $beer): self
    {
        if (!$this->beers->contains($beer)) {
            $this->beers[] = $beer;
            $beer->addCategory($this);
        }
        
        return $this;
    }
    
    public function removeBeer(Beer $beer): self
    {
        if ($this->beers->contains($beer)) {
            $this->beers->removeElement($beer);
            $beer->removeCategory($this);
        }
        
        return $this;
    }
    
}
