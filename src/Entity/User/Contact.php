<?php

namespace App\Entity\User;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="user_contact")
 * @ORM\Entity(repositoryClass="App\Repository\User\ContactRepository")
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
     * @ORM\Column(type="integer")
     */
    private $foursquare;
    
    /**
     * @ORM\Column(type="string")
     */
    private $twitter;
    
    /**
     * @ORM\Column(type="integer")
     */
    private $facebook;
}
