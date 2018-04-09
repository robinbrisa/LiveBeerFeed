<?php

namespace App\Entity\Event;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="event_style")
 * @ORM\Entity(repositoryClass="App\Repository\Event\StyleRepository")
 */
class Style
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
    private $name;
    
    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $header_background_color;
    
    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $header_text_color;
    
    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $link_color;
    
    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $major_info_color;
    
    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $untappd_logo_style;
    
    public function getId()
    {
        return $this->id;
    }
    
    public function __toString()
    {
        return $this->name;
    }
    
    
    /**
     * @return mixed
     */
    public function getHeaderBackgroundColor()
    {
        return $this->header_background_color;
    }

    /**
     * @param mixed $header_background_color
     */
    public function setHeaderBackgroundColor($header_background_color)
    {
        $this->header_background_color = $header_background_color;
    }

    /**
     * @return mixed
     */
    public function getHeaderTextColor()
    {
        return $this->header_text_color;
    }

    /**
     * @param mixed $header_text_color
     */
    public function setHeaderTextColor($header_text_color)
    {
        $this->header_text_color = $header_text_color;
    }

    /**
     * @return mixed
     */
    public function getLinkColor()
    {
        return $this->link_color;
    }

    /**
     * @param mixed $link_color
     */
    public function setLinkColor($link_color)
    {
        $this->link_color = $link_color;
    }
    
    /**
     * @return mixed
     */
    public function getMajorInfoColor()
    {
        return $this->major_info_color;
    }
    
    /**
     * @param mixed $major_info_color
     */
    public function setMajorInfoColor($major_info_color)
    {
        $this->major_info_color = $major_info_color;
    }
    
    /**
     * @return mixed
     */
    public function getUntappdLogoStyle()
    {
        return $this->untappd_logo_style;
    }

    /**
     * @param mixed $untappd_logo_style
     */
    public function setUntappdLogoStyle($untappd_logo_style)
    {
        $this->untappd_logo_style = $untappd_logo_style;
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
