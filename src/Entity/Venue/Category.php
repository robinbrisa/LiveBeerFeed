<?php

namespace App\Entity\Venue;

use Doctrine\ORM\Mapping as ORM;

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
    private $category_name;
    
    /**
     * @ORM\Column(type="boolean")
     */
    private $is_primary;
    
    /**
     * @ORM\ManyToMany(targetEntity="Venue", inversedBy="categories")
     */
    private $venues;
}
