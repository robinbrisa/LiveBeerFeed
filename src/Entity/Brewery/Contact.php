<?php

namespace App\Entity\Brewery;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="brewery_contact")
 * @ORM\Entity(repositoryClass="App\Repository\Brewery\ContactRepository")
 */
class Contact
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
    private $twitter;
    
    /**
     * @ORM\Column(type="string")
     */
    private $facebook;
    
    /**
     * @ORM\Column(type="string")
     */
    private $instagram;
    
    /**
     * @ORM\Column(type="string")
     */
    private $url;
}
