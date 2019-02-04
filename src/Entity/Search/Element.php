<?php

namespace App\Entity\Search;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="search_element")
 * @ORM\Entity(repositoryClass="App\Repository\Search\ElementRepository")
 */
class Element
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;
    
    /**
     * @ORM\ManyToOne(targetEntity="Query", inversedBy="elements")
     * @ORM\JoinColumn(name="element_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $query;
    
    /**
     * @ORM\Column(type="string")
     */
    private $search_string;
    
    /**
     * @ORM\OneToMany(targetEntity="Result", mappedBy="element")
     */
    private $results;
    
    /**
     * @ORM\Column(type="boolean")
     */
    private $finished;

    public function __construct()
    {
        $this->results = new ArrayCollection();
    }
    
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFinished(): ?bool
    {
        return $this->finished;
    }

    public function setFinished(bool $finished): self
    {
        $this->finished = $finished;

        return $this;
    }

    public function getQuery(): ?Query
    {
        return $this->query;
    }

    public function setQuery(?Query $query): self
    {
        $this->query = $query;

        return $this;
    }

    /**
     * @return Collection|Result[]
     */
    public function getResults(): Collection
    {
        return $this->results;
    }

    public function addResult(Result $result): self
    {
        if (!$this->results->contains($result)) {
            $this->results[] = $result;
            $result->setElement($this);
        }

        return $this;
    }

    public function removeResult(Result $result): self
    {
        if ($this->results->contains($result)) {
            $this->results->removeElement($result);
            // set the owning side to null (unless already changed)
            if ($result->getElement() === $this) {
                $result->setElement(null);
            }
        }

        return $this;
    }

    public function getSearchString(): ?string
    {
        return $this->search_string;
    }

    public function setSearchString(string $search_string): self
    {
        $this->search_string = $search_string;

        return $this;
    }
}
