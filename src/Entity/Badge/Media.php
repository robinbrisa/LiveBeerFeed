<?php

namespace App\Entity\Badge;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="badge_media")
 * @ORM\Entity(repositoryClass="App\Repository\Badge\MediaRepository")
 */
class Media
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
    private $badge_image_sm;
    
    /**
     * @ORM\Column(type="string")
     */
    private $badge_image_md;
    
    /**
     * @ORM\Column(type="string")
     */
    private $badge_image_lg;
}
