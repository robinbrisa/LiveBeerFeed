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
     * @ORM\ManyToOne(targetEntity="Checkin", inversedBy="medias", cascade={"remove"})
     * @ORM\JoinColumn(name="checkin_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $checkin;
    
    /**
     * Set id
     *
     * @param integer $id
     *
     * @return Media
     */
    public function setId($id)
    {
        $this->id = $id;
        
        return $this;
    }
    
    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set photoImgSm
     *
     * @param string $photoImgSm
     *
     * @return Media
     */
    public function setPhotoImgSm($photoImgSm)
    {
        $this->photo_img_sm = $photoImgSm;

        return $this;
    }

    /**
     * Get photoImgSm
     *
     * @return string
     */
    public function getPhotoImgSm()
    {
        return $this->photo_img_sm;
    }

    /**
     * Set photoImgMd
     *
     * @param string $photoImgMd
     *
     * @return Media
     */
    public function setPhotoImgMd($photoImgMd)
    {
        $this->photo_img_md = $photoImgMd;

        return $this;
    }

    /**
     * Get photoImgMd
     *
     * @return string
     */
    public function getPhotoImgMd()
    {
        return $this->photo_img_md;
    }

    /**
     * Set photoImgLg
     *
     * @param string $photoImgLg
     *
     * @return Media
     */
    public function setPhotoImgLg($photoImgLg)
    {
        $this->photo_img_lg = $photoImgLg;

        return $this;
    }

    /**
     * Get photoImgLg
     *
     * @return string
     */
    public function getPhotoImgLg()
    {
        return $this->photo_img_lg;
    }

    /**
     * Set photoImgOg
     *
     * @param string $photoImgOg
     *
     * @return Media
     */
    public function setPhotoImgOg($photoImgOg)
    {
        $this->photo_img_og = $photoImgOg;

        return $this;
    }

    /**
     * Get photoImgOg
     *
     * @return string
     */
    public function getPhotoImgOg()
    {
        return $this->photo_img_og;
    }

    /**
     * Set checkin
     *
     * @param \App\Entity\Checkin\Checkin $checkin
     *
     * @return Media
     */
    public function setCheckin(\App\Entity\Checkin\Checkin $checkin = null)
    {
        $this->checkin = $checkin;

        return $this;
    }

    /**
     * Get checkin
     *
     * @return \App\Entity\Checkin\Checkin
     */
    public function getCheckin()
    {
        return $this->checkin;
    }
}
