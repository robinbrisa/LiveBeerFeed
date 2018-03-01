<?php

namespace App\Entity\Checkin;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="checkin_media")
 * @ORM\Entity(repositoryClass="App\Repository\Checkin\MediaRepository")
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
    private $photo_img_sm;
    
    /**
     * @ORM\Column(type="string")
     */
    private $photo_img_md;
    
    /**
     * @ORM\Column(type="string")
     */
    private $photo_img_lg;
    
    /**
     * @ORM\Column(type="string")
     */
    private $photo_img_og;
    
    /**
     * @ORM\ManyToOne(targetEntity="Checkin", inversedBy="medias")
     * @ORM\JoinColumn(name="checkin_id", referencedColumnName="id")
     */
    private $checkin;
}
