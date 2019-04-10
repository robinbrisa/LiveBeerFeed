<?php

namespace App\Entity\Beer;

use App\Entity\Event\TapListItem;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use JMS\Serializer\Annotation as JMS;

/**
 * @ORM\Table(name="beer")
 * @ORM\Entity(repositoryClass="App\Repository\Beer\BeerRepository")
 */
class Beer
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     */
    private $id;
    
    /**
     * @ORM\Column(type="string")
     */
    private $name;
    
    /**
     * @ORM\Column(type="string")
     */
    private $label;
    
    /**
     * @ORM\Column(type="decimal", scale=4, nullable=true)
     */
    private $abv;
    
    /**
     * @ORM\Column(type="decimal", scale=4, nullable=true)
     */
    private $ibu;
    
    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $description;
    
    /**
     * @ORM\ManyToOne(targetEntity="Style", inversedBy="beers")
     * @ORM\JoinColumn(name="style_id", referencedColumnName="id")
     */
    private $style;
    
    /**
     * @ORM\Column(type="boolean")
     */
    private $active;
    
    /**
     * @ORM\Column(type="string")
     */
    private $slug;
    
    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $created_at;
    
    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $rating_count;
    
    /**
     * @ORM\Column(type="decimal", scale=2, nullable=true)
     */
    private $rating_score;
    
    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $total_count;
    
    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $unique_count;
    
    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Brewery\Brewery", inversedBy="beers", cascade={"persist"})
     * @ORM\JoinColumn(name="brewery_id", referencedColumnName="id")
     */
    private $brewery;
        
    /**
     * @ORM\OneToMany(targetEntity="Vintage", mappedBy="parent_id")
     */
    private $vintages;
    
    /**
     * @ORM\OneToMany(targetEntity="Vintage", mappedBy="vintage_id")
     */
    private $parent;
    
    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Checkin\Checkin", mappedBy="beer")
     */
    private $checkins;
    
    /**
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(type="datetime", name="internal_created_at", nullable=true)
     */
    private $internal_created_at;
    
    /**
     * @Gedmo\Timestampable(on="update")
     * @ORM\Column(type="datetime", name="internal_updated_at", nullable=true)
     */
    private $internal_updated_at;
    
    /**
     * @ORM\Column(type="boolean")
     */
    private $needs_refresh = false;
    
    /**
     * @ORM\Column(type="boolean", options={"default":false})
     */
    private $internal_data_gathered = false;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $extra_info;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Event\TapListItem", mappedBy="beer", orphanRemoval=true)
     */
    private $tap_list_items;
    
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->vintages = new \Doctrine\Common\Collections\ArrayCollection();
        $this->parent = new \Doctrine\Common\Collections\ArrayCollection();
        $this->checkins = new \Doctrine\Common\Collections\ArrayCollection();
        $this->tap_list_items = new ArrayCollection();
    }
    
    public function __toString()
    {
        return $this->name . ' (' . $this->brewery->getName() . ')';
    }
    
    /**
     * @JMS\VirtualProperty
     * @JMS\SerializedName("untappd_link")
     */
    public function getUntappdLink() {
        return '<a href="https://untappd.com/b/'.$this->slug.'/'.$this->id.'" target="_blank">'.$this->name.'</a>';
    }
    
    /**
     * Set id
     *
     * @param integer $id
     *
     * @return Beer
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
     * Get $needs_refresh
     *
     * @return boolean
     */
    public function getNeedsRefresh()
    {
        return $this->needs_refresh;
    }
    
    /**
     * Set $needs_refresh
     *
     * @param boolean $needs_refresh
     *
     * @return Beer
     */
    public function setNeedsRefresh($needs_refresh)
    {
        $this->needs_refresh = $needs_refresh;
        
        return $this;
    }
    
    /**
     * Set name
     *
     * @param string $name
     *
     * @return Beer
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set label
     *
     * @param string $label
     *
     * @return Beer
     */
    public function setLabel($label)
    {
        $this->label = $label;

        return $this;
    }

    /**
     * Get label
     *
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * Set abv
     *
     * @param string $abv
     *
     * @return Beer
     */
    public function setAbv($abv)
    {
        $this->abv = $abv;

        return $this;
    }

    /**
     * Get abv
     *
     * @return string
     */
    public function getAbv()
    {
        return $this->abv;
    }

    /**
     * Set ibu
     *
     * @param string $ibu
     *
     * @return Beer
     */
    public function setIbu($ibu)
    {
        $this->ibu = $ibu;

        return $this;
    }

    /**
     * Get ibu
     *
     * @return string
     */
    public function getIbu()
    {
        return $this->ibu;
    }

    /**
     * Set description
     *
     * @param string $description
     *
     * @return Beer
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set style
     *
     * @param string $style
     *
     * @return Beer
     */
    public function setStyle($style)
    {
        $this->style = $style;

        return $this;
    }

    /**
     * Get style
     *
     * @return string
     */
    public function getStyle()
    {
        return $this->style;
    }

    /**
     * Set active
     *
     * @param boolean $active
     *
     * @return Beer
     */
    public function setActive($active)
    {
        $this->active = $active;

        return $this;
    }

    /**
     * Get active
     *
     * @return boolean
     */
    public function getActive()
    {
        return $this->active;
    }

    /**
     * Set slug
     *
     * @param string $slug
     *
     * @return Beer
     */
    public function setSlug($slug)
    {
        $this->slug = $slug;

        return $this;
    }

    /**
     * Get slug
     *
     * @return string
     */
    public function getSlug()
    {
        return $this->slug;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     *
     * @return Beer
     */
    public function setCreatedAt($createdAt)
    {
        $this->created_at = $createdAt;

        return $this;
    }

    /**
     * Get createdAt
     *
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->created_at;
    }

    /**
     * Set ratingCount
     *
     * @param integer $ratingCount
     *
     * @return Beer
     */
    public function setRatingCount($ratingCount)
    {
        $this->rating_count = $ratingCount;

        return $this;
    }

    /**
     * Get ratingCount
     *
     * @return integer
     */
    public function getRatingCount()
    {
        return $this->rating_count;
    }

    /**
     * Set ratingScore
     *
     * @param integer $ratingScore
     *
     * @return Beer
     */
    public function setRatingScore($ratingScore)
    {
        $this->rating_score = $ratingScore;

        return $this;
    }

    /**
     * Get ratingScore
     *
     * @return integer
     */
    public function getRatingScore()
    {
        return $this->rating_score;
    }

    /**
     * Set totalCount
     *
     * @param integer $totalCount
     *
     * @return Beer
     */
    public function setTotalCount($totalCount)
    {
        $this->total_count = $totalCount;

        return $this;
    }

    /**
     * Get totalCount
     *
     * @return integer
     */
    public function getTotalCount()
    {
        return $this->total_count;
    }
    
    /**
     * Set uniqueCount
     *
     * @param integer $uniqueCount
     *
     * @return Beer
     */
    public function setUniqueCount($uniqueCount)
    {
        $this->unique_count = $uniqueCount;
        
        return $this;
    }
    
    /**
     * Get uniqueCount
     *
     * @return integer
     */
    public function getUniqueCount()
    {
        return $this->unique_count;
    }
    
    /**
     * Set brewery
     *
     * @param \App\Entity\Brewery\Brewery $brewery
     *
     * @return Beer
     */
    public function setBrewery(\App\Entity\Brewery\Brewery $brewery = null)
    {
        $this->brewery = $brewery;
        
        return $this;
    }
    
    /**
     * Get brewery
     *
     * @return \App\Entity\Brewery\Brewery
     */
    public function getBrewery()
    {
        return $this->brewery;
    }
    
    /**
     * Set internalCreatedAt
     *
     * @param \DateTime $internalCreatedAt
     *
     * @return Beer
     */
    public function setInternalCreatedAt($internalCreatedAt)
    {
        $this->internal_created_at = $internalCreatedAt;

        return $this;
    }

    /**
     * Get internalCreatedAt
     *
     * @return \DateTime
     */
    public function getInternalCreatedAt()
    {
        return $this->internal_created_at;
    }

    /**
     * Set internalUpdatedAt
     *
     * @param \DateTime $internalUpdatedAt
     *
     * @return Beer
     */
    public function setInternalUpdatedAt($internalUpdatedAt)
    {
        $this->internal_updated_at = $internalUpdatedAt;

        return $this;
    }

    /**
     * Get internalUpdatedAt
     *
     * @return \DateTime
     */
    public function getInternalUpdatedAt()
    {
        return $this->internal_updated_at;
    }

    /**
     * Set internalDataGathered
     *
     * @param boolean $internalDataGathered
     *
     * @return Beer
     */
    public function setInternalDataGathered($internalDataGathered)
    {
        $this->internal_data_gathered = $internalDataGathered;

        return $this;
    }

    /**
     * Get internalDataGathered
     *
     * @return boolean
     */
    public function getInternalDataGathered()
    {
        return $this->internal_data_gathered;
    }

    /**
     * Add vintage
     *
     * @param \App\Entity\Beer\Vintage $vintage
     *
     * @return Beer
     */
    public function addVintage(\App\Entity\Beer\Vintage $vintage)
    {
        $this->vintages[] = $vintage;

        return $this;
    }

    /**
     * Remove vintage
     *
     * @param \App\Entity\Beer\Vintage $vintage
     */
    public function removeVintage(\App\Entity\Beer\Vintage $vintage)
    {
        $this->vintages->removeElement($vintage);
    }

    /**
     * Get vintages
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getVintages()
    {
        return $this->vintages;
    }

    /**
     * Add parent
     *
     * @param \App\Entity\Beer\Vintage $parent
     *
     * @return Beer
     */
    public function addParent(\App\Entity\Beer\Vintage $parent)
    {
        $this->parent[] = $parent;

        return $this;
    }

    /**
     * Remove parent
     *
     * @param \App\Entity\Beer\Vintage $parent
     */
    public function removeParent(\App\Entity\Beer\Vintage $parent)
    {
        $this->parent->removeElement($parent);
    }

    /**
     * Get parent
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * Add checkin
     *
     * @param \App\Entity\Checkin\Checkin $checkin
     *
     * @return Beer
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

    public function getExtraInfo(): ?string
    {
        return $this->extra_info;
    }

    public function setExtraInfo(?string $extra_info): self
    {
        $this->extra_info = $extra_info;

        return $this;
    }

    /**
     * @return Collection|TapListItem[]
     */
    public function getTapListItems(): Collection
    {
        return $this->tap_list_items;
    }

    public function addTapListItem(TapListItem $tapListItem): self
    {
        if (!$this->tap_list_items->contains($tapListItem)) {
            $this->tap_list_items[] = $tapListItem;
            $tapListItem->setBeer($this);
        }

        return $this;
    }

    public function removeTapListItem(TapListItem $tapListItem): self
    {
        if ($this->tap_list_items->contains($tapListItem)) {
            $this->tap_list_items->removeElement($tapListItem);
            // set the owning side to null (unless already changed)
            if ($tapListItem->getBeer() === $this) {
                $tapListItem->setBeer(null);
            }
        }

        return $this;
    }
}
