<?php

namespace App\Entity\Checkin;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="checkin_source",
 *    uniqueConstraints={
 *        @ORM\UniqueConstraint(name="source_unique", 
 *            columns={"app_name", "app_website"})
 *    })
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
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->checkins = new \Doctrine\Common\Collections\ArrayCollection();
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
     * Set appName
     *
     * @param string $appName
     *
     * @return Source
     */
    public function setAppName($appName)
    {
        $this->app_name = $appName;

        return $this;
    }

    /**
     * Get appName
     *
     * @return string
     */
    public function getAppName()
    {
        return $this->app_name;
    }

    /**
     * Set appWebsite
     *
     * @param string $appWebsite
     *
     * @return Source
     */
    public function setAppWebsite($appWebsite)
    {
        $this->app_website = $appWebsite;

        return $this;
    }

    /**
     * Get appWebsite
     *
     * @return string
     */
    public function getAppWebsite()
    {
        return $this->app_website;
    }

    /**
     * Add checkin
     *
     * @param \App\Entity\Checkin\Checkin $checkin
     *
     * @return Source
     */
    public function addCheckin(\App\Entity\Checkin\Checkin $checkin)
    {
        $this->checkins[] = $checkin;

        return $this;
    }

    /**
     * Remove checkin
     *
     * @param \App\Entity\Checkin\Checkin $checkin
     */
    public function removeCheckin(\App\Entity\Checkin\Checkin $checkin)
    {
        $this->checkins->removeElement($checkin);
    }

    /**
     * Get checkins
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getCheckins()
    {
        return $this->checkins;
    }
}
