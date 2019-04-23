<?php

namespace App\Entity\Beer;

use App\Entity\Event\Publisher;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Entity(repositoryClass="App\Repository\Beer\LocalBeerRepository")
 */
class LocalBeer
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=3, nullable=true)
     */
    private $abv;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=4, nullable=true)
     */
    private $ibu;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $brewery;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $extra_info;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $style;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Event\Publisher", inversedBy="local_beers")
     * @ORM\JoinColumn(nullable=false)
     */
    private $owner;
    
    /**
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $created_at;
    
    /**
     * @Gedmo\Timestampable(on="update")
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $updated_at;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getAbv()
    {
        return $this->abv;
    }

    public function setAbv($abv): self
    {
        $this->abv = $abv;

        return $this;
    }

    public function getIbu()
    {
        return $this->ibu;
    }

    public function setIbu($ibu): self
    {
        $this->ibu = $ibu;

        return $this;
    }

    public function getBrewery(): ?string
    {
        return $this->brewery;
    }

    public function setBrewery(string $brewery): self
    {
        $this->brewery = $brewery;

        return $this;
    }

    public function getExtraInfo(): ?string
    {
        return $this->extra_info;
    }

    public function setExtraInfo(?string $extra_info): self
    {
        $this->extra_info = $extra_info;

        return $this;
    }

    public function getStyle(): ?string
    {
        return $this->style;
    }

    public function setStyle(string $style): self
    {
        $this->style = $style;

        return $this;
    }

    public function getOwner(): ?Publisher
    {
        return $this->owner;
    }

    public function setOwner(?Publisher $owner): self
    {
        $this->owner = $owner;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->created_at;
    }

    public function setCreatedAt(?\DateTimeInterface $created_at): self
    {
        $this->created_at = $created_at;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updated_at;
    }

    public function setUpdatedAt(?\DateTimeInterface $updated_at): self
    {
        $this->updated_at = $updated_at;

        return $this;
    }
}
