<?php

namespace App\Entity\Event;

use App\Entity\Beer\Beer;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\Event\TapListItemRepository")
 * @ORM\Table(name="event_taplist")
 */
class TapListItem
{
    /**
     * @ORM\Id()
     * @ORM\ManyToOne(targetEntity="App\Entity\Event\Session", inversedBy="tap_list_items")
     * @ORM\JoinColumn(nullable=false)
     */
    private $session;

    /**
     * @ORM\Id()
     * @ORM\ManyToOne(targetEntity="App\Entity\Beer\Beer", inversedBy="tap_list_items")
     * @ORM\JoinColumn(nullable=false)
     */
    private $beer;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Event\Publisher", inversedBy="tap_list_items")
     */
    private $owner;

    /**
     * @ORM\Column(type="boolean")
     */
    private $out_of_stock;

    public function getSession(): ?Session
    {
        return $this->session;
    }

    public function setSession(?Session $session): self
    {
        $this->session = $session;

        return $this;
    }

    public function getBeer(): ?Beer
    {
        return $this->beer;
    }

    public function setBeer(?Beer $beer): self
    {
        $this->beer = $beer;

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

    public function getOutOfStock(): ?bool
    {
        return $this->out_of_stock;
    }

    public function setOutOfStock(bool $out_of_stock): self
    {
        $this->out_of_stock = $out_of_stock;

        return $this;
    }
}
