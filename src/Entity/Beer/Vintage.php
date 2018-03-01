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
    
}
