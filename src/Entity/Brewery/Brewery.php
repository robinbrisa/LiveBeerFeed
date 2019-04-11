<?php

namespace App\Entity\Brewery;

use App\Entity\Beer\Beer;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use JMS\Serializer\Annotation as JMS;

/**
 * @ORM\Table(name="brewery")
 * @ORM\Entity(repositoryClass="App\Repository\Brewery\BreweryRepository")
 */
class Brewery
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
     * @ORM\Column(type="string", nullable=true)
     */
    private $slug;
    
    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $created_at;
    
    /**
     * @ORM\Column(type="string")
     */
    private $label;
    
    /**
     * @ORM\Column(type="string")
     */
    private $country_name;
        
    /**
     * @ORM\Column(type="boolean")
     */
    private $active;
    
    /**
     * @ORM\Column(type="boolean")
     */
    private $is_independent;
    
    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $beer_count;
    
    /**
     * @ORM\ManyToOne(targetEntity="Type", inversedBy="breweries")
     * @ORM\JoinColumn(name="type_id", referencedColumnName="id")
     */
    private $type;
    
    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $ratings_count;
    
    /**
     * @ORM\Column(type="decimal", scale=2, nullable=true)
     */
    private $rating_score;
    
    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $description;
    
    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $total_count;
    
    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $unique_count;
    
    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $monthly_count;
    
    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $weekly_count;
    
    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $twitter;
    
    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $facebook;
    
    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $instagram;
    
    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $url;
    
    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $address;
    
    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $city;
    
    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $state;
    
    /**
     * @ORM\Column(type="decimal", scale=4, nullable=true)
     */
    private $latitude;
    
    /**
     * @ORM\Column(type="decimal", scale=4, nullable=true)
     */
    private $longitude;
    
    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $is_claimed;
    
    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $claimed_slug;
    
    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $follower_count;
    
    /**
     * @ORM\Column(type="integer", nullable=true)
     * @ORM\OneToOne(targetEntity="User")
     * @ORM\JoinColumn(name="claim_user_id", referencedColumnName="id")
     */
    private $claim_user;
    
    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Beer\Beer", mappedBy="brewery", cascade={"persist"})
     */
    private $beers;
    
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
     * @ORM\Column(type="boolean", options={"default":false})
     */
    private $internal_data_gathered;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Beer\Beer", mappedBy="collaborating_breweries")
     */
    private $collaborations;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->beers = new \Doctrine\Common\Collections\ArrayCollection();
        $this->collaborations = new ArrayCollection();
    }
    
    public function __toString()
    {
        return $this->name;
    }
    
    /**
     * @JMS\VirtualProperty
     * @JMS\SerializedName("untappd_link")
     */
    public function getUntappdLink() {
        return '<a href="https://untappd.com/w/'.$this->slug.'/'.$this->id.'" target="_blank">'.$this->name.'</a>';
    }
    
    /**
     * Set id
     *
     * @param integer $id
     *
     * @return Brewery
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
     * Set name
     *
     * @param string $name
     *
     * @return Brewery
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
     * Set slug
     *
     * @param string $slug
     *
     * @return Brewery
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
     * @return Brewery
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
     * Set label
     *
     * @param string $label
     *
     * @return Brewery
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
     * Set countryName
     *
     * @param string $countryName
     *
     * @return Brewery
     */
    public function setCountryName($countryName)
    {
        $this->country_name = $countryName;

        return $this;
    }

    /**
     * Get countryName
     *
     * @return string
     */
    public function getCountryName()
    {
        return $this->country_name;
    }

    /**
     * Set active
     *
     * @param boolean $active
     *
     * @return Brewery
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
     * Set isIndependent
     *
     * @param boolean $isIndependent
     *
     * @return Brewery
     */
    public function setIsIndependent($isIndependent)
    {
        $this->is_independent = $isIndependent;

        return $this;
    }

    /**
     * Get isIndependent
     *
     * @return boolean
     */
    public function getIsIndependent()
    {
        return $this->is_independent;
    }

    /**
     * Set beerCount
     *
     * @param integer $beerCount
     *
     * @return Brewery
     */
    public function setBeerCount($beerCount)
    {
        $this->beer_count = $beerCount;

        return $this;
    }

    /**
     * Get beerCount
     *
     * @return integer
     */
    public function getBeerCount()
    {
        return $this->beer_count;
    }

    /**
     * Set type
     *
     * @param string $type
     *
     * @return Brewery
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set description
     *
     * @param string $description
     *
     * @return Brewery
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
     * Set internalCreatedAt
     *
     * @param \DateTime $internalCreatedAt
     *
     * @return Brewery
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
     * @return Brewery
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
     * @return Brewery
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
     * Set ratingsCount
     *
     * @param integer $ratingsCount
     *
     * @return Brewery
     */
    public function setRatingsCount($ratingsCount)
    {
        $this->ratings_count = $ratingsCount;
        
        return $this;
    }
    
    /**
     * Get ratingsCount
     *
     * @return integer
     */
    public function getRatingsCount()
    {
        return $this->ratings_count;
    }
    
    /**
     * Set ratingScore
     *
     * @param string $ratingScore
     *
     * @return Brewery
     */
    public function setRatingScore($ratingScore)
    {
        $this->rating_score = $ratingScore;
        
        return $this;
    }
    
    /**
     * Get ratingScore
     *
     * @return string
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
     * @return Brewery
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
     * @return Brewery
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
     * Set monthlyCount
     *
     * @param integer $monthlyCount
     *
     * @return Brewery
     */
    public function setMonthlyCount($monthlyCount)
    {
        $this->monthly_count = $monthlyCount;
        
        return $this;
    }
    
    /**
     * Get monthlyCount
     *
     * @return integer
     */
    public function getMonthlyCount()
    {
        return $this->monthly_count;
    }
    
    /**
     * Set weeklyCount
     *
     * @param integer $weeklyCount
     *
     * @return Brewery
     */
    public function setWeeklyCount($weeklyCount)
    {
        $this->weekly_count = $weeklyCount;
        
        return $this;
    }
    
    /**
     * Get weeklyCount
     *
     * @return integer
     */
    public function getWeeklyCount()
    {
        return $this->weekly_count;
    }
    
    /**
     * Set twitter
     *
     * @param string $twitter
     *
     * @return Brewery
     */
    public function setTwitter($twitter)
    {
        $this->twitter = $twitter;
        
        return $this;
    }
    
    /**
     * Get twitter
     *
     * @return string
     */
    public function getTwitter()
    {
        return $this->twitter;
    }
    
    /**
     * Set facebook
     *
     * @param string $facebook
     *
     * @return Brewery
     */
    public function setFacebook($facebook)
    {
        $this->facebook = $facebook;
        
        return $this;
    }
    
    /**
     * Get facebook
     *
     * @return string
     */
    public function getFacebook()
    {
        return $this->facebook;
    }
    
    /**
     * Set instagram
     *
     * @param string $instagram
     *
     * @return Brewery
     */
    public function setInstagram($instagram)
    {
        $this->instagram = $instagram;
        
        return $this;
    }
    
    /**
     * Get instagram
     *
     * @return string
     */
    public function getInstagram()
    {
        return $this->instagram;
    }
    
    /**
     * Set url
     *
     * @param string $url
     *
     * @return Brewery
     */
    public function setUrl($url)
    {
        $this->url = $url;
        
        return $this;
    }
    
    /**
     * Get url
     *
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }
    
    /**
     * Set address
     *
     * @param string $breweryAddress
     *
     * @return Brewery
     */
    public function setAddress($breweryAddress)
    {
        $this->address = $breweryAddress;
        
        return $this;
    }
    
    /**
     * Get address
     *
     * @return string
     */
    public function getAddress()
    {
        return $this->address;
    }
    
    /**
     * Set city
     *
     * @param string $breweryCity
     *
     * @return Brewery
     */
    public function setCity($breweryCity)
    {
        $this->city = $breweryCity;
        
        return $this;
    }
    
    /**
     * Get city
     *
     * @return string
     */
    public function getCity()
    {
        return $this->city;
    }
    
    /**
     * Set state
     *
     * @param string $breweryState
     *
     * @return Brewery
     */
    public function setState($breweryState)
    {
        $this->state = $breweryState;
        
        return $this;
    }
    
    /**
     * Get state
     *
     * @return string
     */
    public function getState()
    {
        return $this->state;
    }
    
    /**
     * Set latitude
     *
     * @param string $breweryLatitude
     *
     * @return Brewery
     */
    public function setLatitude($breweryLatitude)
    {
        $this->latitude = $breweryLatitude;
        
        return $this;
    }
    
    /**
     * Get latitude
     *
     * @return string
     */
    public function getLatitude()
    {
        return $this->latitude;
    }
    
    /**
     * Set Longitude
     *
     * @param string $breweryLongitude
     *
     * @return Brewery
     */
    public function setLongitude($breweryLongitude)
    {
        $this->longitude = $breweryLongitude;
        
        return $this;
    }
    
    /**
     * Get Longitude
     *
     * @return string
     */
    public function getLongitude()
    {
        return $this->longitude;
    }
    
    /**
     * Set isClaimed
     *
     * @param boolean $isClaimed
     *
     * @return Brewery
     */
    public function setIsClaimed($isClaimed)
    {
        $this->is_claimed = $isClaimed;
        
        return $this;
    }
    
    /**
     * Get isClaimed
     *
     * @return boolean
     */
    public function getIsClaimed()
    {
        return $this->is_claimed;
    }
    
    /**
     * Set claimedSlug
     *
     * @param string $claimedSlug
     *
     * @return Brewery
     */
    public function setClaimedSlug($claimedSlug)
    {
        $this->claimed_slug = $claimedSlug;
        
        return $this;
    }
    
    /**
     * Get claimedSlug
     *
     * @return string
     */
    public function getClaimedSlug()
    {
        return $this->claimed_slug;
    }
    
    /**
     * Set followerCount
     *
     * @param integer $followerCount
     *
     * @return Brewery
     */
    public function setFollowerCount($followerCount)
    {
        $this->follower_count = $followerCount;
        
        return $this;
    }
    
    /**
     * Get followerCount
     *
     * @return integer
     */
    public function getFollowerCount()
    {
        return $this->follower_count;
    }
    
    /**
     * Set claimUser
     *
     * @param integer $claim_user
     *
     * @return Brewery
     */
    public function setClaimUser($claim_user)
    {
        $this->claim_user = $claim_user;
        
        return $this;
    }
    
    /**
     * Get claimUser
     *
     * @return integer
     */
    public function getClaimUser()
    {
        return $this->claim_user;
    }
    
    /**
     * Add beer
     *
     * @param \App\Entity\Beer\Beer $beer
     *
     * @return Brewery
     */
    public function addBeer(\App\Entity\Beer\Beer $beer)
    {
        $this->beers[] = $beer;

        return $this;
    }

    /**
     * Remove beer
     *
     * @param \App\Entity\Beer\Beer $beer
     */
    public function removeBeer(\App\Entity\Beer\Beer $beer)
    {
        $this->beers->removeElement($beer);
    }

    /**
     * Get beers
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getBeers()
    {
        return $this->beers;
    }

    /**
     * @return Collection|Beer[]
     */
    public function getCollaborations(): Collection
    {
        return $this->collaborations;
    }

    public function addCollaboration(Beer $collaboration): self
    {
        if (!$this->collaborations->contains($collaboration)) {
            $this->collaborations[] = $collaboration;
            $collaboration->addCollaboratingBrewery($this);
        }

        return $this;
    }

    public function removeCollaboration(Beer $collaboration): self
    {
        if ($this->collaborations->contains($collaboration)) {
            $this->collaborations->removeElement($collaboration);
            $collaboration->removeCollaboratingBrewery($this);
        }

        return $this;
    }

}
