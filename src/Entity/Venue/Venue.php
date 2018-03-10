<?php

namespace App\Entity\Venue;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Table(name="venue")
 * @ORM\Entity(repositoryClass="App\Repository\Venue\VenueRepository")
 */
class Venue
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
    private $slug;
    
    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $last_updated;
    
    /**
     * @ORM\Column(type="string")
     */
    private $main_category;
    
    /**
     * @ORM\ManyToMany(targetEntity="Category", inversedBy="venues", cascade={"persist"})
     * @ORM\JoinTable(name="rel_venue_category")
     */
    private $categories;
    
    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $total_count;
    
    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $monthly_count;
    
    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $weekly_count;
    
    /**
     * @ORM\Column(type="string")
     */
    private $icon_sm;
    
    /**
     * @ORM\Column(type="string")
     */
    private $icon_md;
    
    /**
     * @ORM\Column(type="string")
     */
    private $icon_lg;
    
    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $public_venue;
    
    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $twitter;
    
    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $venue_url;
    
    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $facebook;
    
    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $yelp;
    
    /**
     * @ORM\Column(type="string")
     */
    private $foursquare_id;
    
    /**
     * @ORM\Column(type="string")
     */
    private $foursquare_url;
    
    /**
     * @ORM\Column(type="boolean")
     */
    private $is_verified;
    
    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $is_closed;
    
    /**
     * @ORM\Column(type="string")
     */
    private $address;
    
    /**
     * @ORM\Column(type="string")
     */
    private $city;
    
    /**
     * @ORM\Column(type="string")
     */
    private $state;
    
    /**
     * @ORM\Column(type="string")
     */
    private $country;
    
    /**
     * @ORM\Column(type="decimal", scale=4)
     */
    private $latitude;
    
    /**
     * @ORM\Column(type="decimal", scale=4)
     */
    private $longitude;
    
    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Checkin\Checkin", mappedBy="venue")
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
     * @ORM\Column(type="boolean", options={"default":false})
     */
    private $internal_data_gathered = false;
    
    /**
     * @ORM\Column(type="boolean", options={"default":false})
     */
    private $internal_full_history_gathered = false;
    
    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $internal_full_history_last_max_id;
    
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->categories = new \Doctrine\Common\Collections\ArrayCollection();
        $this->checkins = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Set id
     *
     * @param integer $id
     *
     * @return Venue
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
     * @return Venue
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
     * @return Venue
     */
    public function setSlug($slug)
    {
        $this->slug = $slug;

        return $this;
    }

    /**
     * Get venueSlug
     *
     * @return string
     */
    public function getSlug()
    {
        return $this->slug;
    }

    /**
     * Set lastUpdated
     *
     * @param \DateTime $lastUpdated
     *
     * @return Venue
     */
    public function setLastUpdated($lastUpdated)
    {
        $this->last_updated = $lastUpdated;

        return $this;
    }

    /**
     * Get lastUpdated
     *
     * @return \DateTime
     */
    public function getLastUpdated()
    {
        return $this->last_updated;
    }

    /**
     * Set publicVenue
     *
     * @param boolean $publicVenue
     *
     * @return Venue
     */
    public function setPublicVenue($publicVenue)
    {
        $this->public_venue = $publicVenue;

        return $this;
    }

    /**
     * Get publicVenue
     *
     * @return boolean
     */
    public function getPublicVenue()
    {
        return $this->public_venue;
    }
    
    /**
     * Set main_category
     *
     * @param string $main_category
     *
     * @return Venue
     */
    public function setMainCategory($main_category)
    {
        $this->main_category = $main_category;
        
        return $this;
    }
    
    /**
     * Get main_category
     *
     * @return string
     */
    public function getMainCategory()
    {
        return $this->main_category;
    }
    
    /**
     * Add category
     *
     * @param \App\Entity\Venue\Category $category
     *
     * @return Venue
     */
    public function addCategory(\App\Entity\Venue\Category $category)
    {
        if (!$this->categories->contains($category)) {
            $this->categories[] = $category;
        }

        return $this;
    }

    /**
     * Remove category
     *
     * @param \App\Entity\Venue\Category $category
     */
    public function removeCategory(\App\Entity\Venue\Category $category)
    {
        $this->categories->removeElement($category);
    }

    /**
     * Get categories
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getCategories()
    {
        return $this->categories;
    }
    
    
    /**
     * Reset categories
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function resetCategories()
    {
        $this->categories = new \Doctrine\Common\Collections\ArrayCollection();
        
        return $this->categories;
    }
    
    /**
     * Set totalCount
     *
     * @param integer $totalCount
     *
     * @return Venue
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
     * Set monthlyCount
     *
     * @param integer $monthlyCount
     *
     * @return Venue
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
     * @return Venue
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
     * Set icon_sm
     *
     * @param string $icon_sm
     *
     * @return Venue
     */
    public function setIconSm($icon_sm)
    {
        $this->icon_sm = $icon_sm;
        
        return $this;
    }
    
    /**
     * Get icon_sm
     *
     * @return string
     */
    public function getIconSm()
    {
        return $this->icon_sm;
    }
    
    /**
     * Set icon_md
     *
     * @param string $icon_md
     *
     * @return Venue
     */
    public function setIconMd($icon_md)
    {
        $this->icon_md = $icon_md;
        
        return $this;
    }
    
    /**
     * Get icon_md
     *
     * @return string
     */
    public function getIconMd()
    {
        return $this->icon_md;
    }
    
    /**
     * Set icon_lg
     *
     * @param string $icon_lg
     *
     * @return Venue
     */
    public function setIconLg($icon_lg)
    {
        $this->icon_lg = $icon_lg;
        
        return $this;
    }
    
    /**
     * Get icon_lg
     *
     * @return string
     */
    public function getIconLg()
    {
        return $this->icon_lg;
    }
    
    /**
     * Set twitter
     *
     * @param string $twitter
     *
     * @return Venue
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
     * Set venueUrl
     *
     * @param string $venueUrl
     *
     * @return Venue
     */
    public function setVenueUrl($venueUrl)
    {
        $this->venue_url = $venueUrl;
        
        return $this;
    }
    
    /**
     * Get venueUrl
     *
     * @return string
     */
    public function getVenueUrl()
    {
        return $this->venue_url;
    }
    
    /**
     * Set facebook
     *
     * @param string $facebook
     *
     * @return Venue
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
     * Set yelp
     *
     * @param string $yelp
     *
     * @return Venue
     */
    public function setYelp($yelp)
    {
        $this->yelp = $yelp;
        
        return $this;
    }
    
    /**
     * Get yelp
     *
     * @return string
     */
    public function getYelp()
    {
        return $this->yelp;
    }
    
    /**
     * Set foursquareId
     *
     * @param string $foursquareId
     *
     * @return Venue
     */
    public function setFoursquareId($foursquareId)
    {
        $this->foursquare_id = $foursquareId;
        
        return $this;
    }
    
    /**
     * Get foursquareId
     *
     * @return string
     */
    public function getFoursquareId()
    {
        return $this->foursquare_id;
    }
    
    /**
     * Set foursquareUrl
     *
     * @param string $foursquareUrl
     *
     * @return Venue
     */
    public function setFoursquareUrl($foursquareUrl)
    {
        $this->foursquare_url = $foursquareUrl;
        
        return $this;
    }
    
    /**
     * Get foursquareUrl
     *
     * @return string
     */
    public function getFoursquareUrl()
    {
        return $this->foursquare_url;
    }
    
    /**
     * Set isVerified
     *
     * @param boolean $isVerified
     *
     * @return Venue
     */
    public function setIsVerified($isVerified)
    {
        $this->is_verified = $isVerified;
        
        return $this;
    }
    
    /**
     * Get isVerified
     *
     * @return boolean
     */
    public function getIsVerified()
    {
        return $this->is_verified;
    }
    
    /**
     * Set isClosed
     *
     * @param boolean $isClosed
     *
     * @return Venue
     */
    public function setIsClosed($isClosed)
    {
        $this->is_closed = $isClosed;
        
        return $this;
    }
    
    /**
     * Get isClosed
     *
     * @return boolean
     */
    public function getIsClosed()
    {
        return $this->is_closed;
    }
    
    /**
     * Set Address
     *
     * @param string $address
     *
     * @return Venue
     */
    public function setAddress($address)
    {
        $this->address = $address;
        
        return $this;
    }
    
    /**
     * Get Address
     *
     * @return string
     */
    public function getAddress()
    {
        return $this->address;
    }
    
    /**
     * Set City
     *
     * @param string $city
     *
     * @return Venue
     */
    public function setCity($city)
    {
        $this->city = $city;
        
        return $this;
    }
    
    /**
     * Get City
     *
     * @return string
     */
    public function getCity()
    {
        return $this->city;
    }
    
    /**
     * Set State
     *
     * @param string $State
     *
     * @return Venue
     */
    public function setState($State)
    {
        $this->state = $State;
        
        return $this;
    }
    
    /**
     * Get State
     *
     * @return string
     */
    public function getState()
    {
        return $this->state;
    }
    
    /**
     * Set Country
     *
     * @param string $country
     *
     * @return Venue
     */
    public function setCountry($country)
    {
        $this->country = $country;
        
        return $this;
    }
    
    /**
     * Get Country
     *
     * @return string
     */
    public function getCountry()
    {
        return $this->country;
    }
    
    /**
     * Set latitude
     *
     * @param string $latitude
     *
     * @return Venue
     */
    public function setLatitude($latitude)
    {
        $this->latitude = $latitude;
        
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
     * Set longitude
     *
     * @param string $longitude
     *
     * @return Venue
     */
    public function setLongitude($longitude)
    {
        $this->longitude = $longitude;
        
        return $this;
    }
    
    /**
     * Get longitude
     *
     * @return string
     */
    public function getLongitude()
    {
        return $this->longitude;
    }
    
    /**
     * Add checkin
     *
     * @param \App\Entity\Checkin\Checkin $checkin
     *
     * @return Venue
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
    
    
    /**
     * Set internalCreatedAt
     *
     * @param \DateTime $internalCreatedAt
     *
     * @return Venue
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
     * @return Venue
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
     * @return Venue
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
     * @return boolean
     */
    public function getInternalFullHistoryGathered()
    {
        return $this->internal_full_history_gathered;
    }
    
    /**
     * @param boolean $internalFullHistoryGathered
     */
    public function setInternalFullHistoryGathered($internalFullHistoryGathered)
    {
        $this->internal_full_history_gathered = $internalFullHistoryGathered;
        
        return $this;
    }
    
    /**
     * @return mixed
     */
    public function getInternalFullHistoryLastMaxId()
    {
        return $this->internal_full_history_last_max_id;
    }
    
    
    /**
     * @param mixed $internalFullHistoryLastMaxId
     */
    public function setInternalFullHistoryLastMaxId($internalFullHistoryLastMaxId)
    {
        $this->internal_full_history_last_max_id = $internalFullHistoryLastMaxId;
        
        return $this;
    }
    
}
