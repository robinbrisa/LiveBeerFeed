<?php

namespace App\Entity\Checkin;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="checkin_source")
 * @ORM\Entity(repositoryClass="App\Repository\Checkin\SourceRepository")
 */
class Source
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
    private $app_name;
    
    /**
     * @ORM\Column(type="string")
     */
    private $app_website;
    
    /**
     * @ORM\OneToMany(targetEntity="Checkin", mappedBy="source")
     */
    private $checkins;
}
