<?php

namespace App\Entity\Search;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="search_result")
 * @ORM\Entity(repositoryClass="App\Repository\Search\ResultRepository")
 */
class Result
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;
    
    /**
     * @ORM\ManyToOne(targetEntity="Element", inversedBy="results")
     * @ORM\JoinColumn(name="element_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $element;
    
    /**
     * @ORM\Column(type="boolean")
     */
    private $selected;
    
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getElement(): ?Element
    {
        return $this->element;
    }

    public function setElement(?Element $element): self
    {
        $this->element = $element;

        return $this;
    }

    public function getSelected(): ?bool
    {
        return $this->selected;
    }

    public function setSelected(bool $selected): self
    {
        $this->selected = $selected;

        return $this;
    }
}
