<?php

namespace App\Entity\Beer;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="beer_vintage")
 * @ORM\Entity(repositoryClass="App\Repository\Beer\VintageRepository")
 */
class Vintage
{
    /**
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="Beer", inversedBy="vintages")
     * @ORM\JoinColumn(name="parent_id", referencedColumnName="id")
     */
    private $parent_id;
    
    /**
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="Beer", inversedBy="parent")
     * @ORM\JoinColumn(name="vintage_id", referencedColumnName="id")
     */
    private $vintage_id;
    
    /**
     * @ORM\Column(type="boolean")
     */
    private $is_vintage;
    
    /**
     * @ORM\Column(type="boolean")
     */
    private $is_variant;
    

    /**
     * Set isVintage
     *
     * @param boolean $isVintage
     *
     * @return Vintage
     */
    public function setIsVintage($isVintage)
    {
        $this->is_vintage = $isVintage;

        return $this;
    }

    /**
     * Get isVintage
     *
     * @return boolean
     */
    public function getIsVintage()
    {
        return $this->is_vintage;
    }

    /**
     * Set isVariant
     *
     * @param boolean $isVariant
     *
     * @return Vintage
     */
    public function setIsVariant($isVariant)
    {
        $this->is_variant = $isVariant;

        return $this;
    }

    /**
     * Get isVariant
     *
     * @return boolean
     */
    public function getIsVariant()
    {
        return $this->is_variant;
    }

    /**
     * Set parentId
     *
     * @param \App\Entity\Beer\Beer $parentId
     *
     * @return Vintage
     */
    public function setParentId(\App\Entity\Beer\Beer $parentId)
    {
        $this->parent_id = $parentId;

        return $this;
    }

    /**
     * Get parentId
     *
     * @return \App\Entity\Beer\Beer
     */
    public function getParentId()
    {
        return $this->parent_id;
    }

    /**
     * Set vintageId
     *
     * @param \App\Entity\Beer\Beer $vintageId
     *
     * @return Vintage
     */
    public function setVintageId(\App\Entity\Beer\Beer $vintageId)
    {
        $this->vintage_id = $vintageId;

        return $this;
    }

    /**
     * Get vintageId
     *
     * @return \App\Entity\Beer\Beer
     */
    public function getVintageId()
    {
        return $this->vintage_id;
    }
}
