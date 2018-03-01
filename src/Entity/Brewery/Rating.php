<?php

namespace App\Entity\Brewery;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="brewery_rating")
 * @ORM\Entity(repositoryClass="App\Repository\Brewery\RatingRepository")
 */
class Rating
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;
    
    /**
     * @ORM\Column(type="integer")
     */
    private $count;
    
    /**
     * @ORM\Column(type="decimal")
     */
    private $rating_score;
}
