<?php

namespace App\Entity\User;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use JMS\Serializer\Annotation as JMS;

/**
 * @ORM\Table(name="user")
 * @ORM\Entity(repositoryClass="App\Repository\User\UserRepository")
 */
class User
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     */
    private $id;
    
    /**
     * @ORM\Column(type="string")
     */
    private $user_name;
    
    /**
     * @ORM\Column(type="string")
     */
    private $first_name;
    
    /**
     * @ORM\Column(type="string")
     */
    private $last_name;
    
    /**
     * @ORM\Column(type="string")
     */
    private $user_avatar;
    
    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $user_avatar_hd;
    
    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $user_cover_photo;
    
    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $user_cover_photo_offset;
    
    /**
     * @ORM\Column(type="boolean")
     */
    private $is_private = false;
    
    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $location;
    
    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $url;
    
    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $bio;
    
    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $is_supporter;
    
    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $is_moderator;
    
    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $untappd_url;
    
    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $account_type;
    
    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $total_badges;
    
    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $total_friends;
    
    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $total_checkins;
    
    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $total_beers;
    
    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $total_created_beers;
    
    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $total_followings;
    
    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $total_photos;
    
    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $foursquare;
    
    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $twitter;
    
    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $facebook;
        
    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $date_joined;
    
    /**
     * @ORM\OneToMany(targetEntity="Friendship", mappedBy="user", orphanRemoval=true)
     * @JMS\Exclude()
     */
    private $friends;
    
    /**
     * @ORM\OneToMany(targetEntity="Friendship", mappedBy="friend", cascade={"persist"})
     * @JMS\Exclude()
     */
    private $friendsWithUser;
    
    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Checkin\Checkin", mappedBy="user", cascade={"persist"})
     */
    private $checkins;
    
    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Checkin\Comment", mappedBy="user")
     */
    private $comments;
    
    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Checkin\Toast", mappedBy="user")
     */
    private $toasts;
    
    /**
     * @ORM\OneToMany(targetEntity="\App\Entity\Badge\BadgeRelation", mappedBy="user")
     */
    private $badge_relation;
    
    /**
     * @ORM\Column(name="internal_untappd_access_token", type="string", nullable=true, unique=true)
     */
    private $internal_untappd_access_token;
    
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
     * @ORM\Column(type="datetime", name="internal_latest_checkin_refresh", nullable=true)
     */
    private $internal_latest_checkin_refresh;
    
    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $internal_friendlist_last_offset;
    
    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Event\Event", inversedBy="users_attending")
     * @ORM\JoinTable(name="user_event_attendance")
     */
    private $attending;
    
    /**
     * @ORM\OneToMany(targetEntity="SavedData", mappedBy="user")
     */
    private $saved_data;
    

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->friends = new \Doctrine\Common\Collections\ArrayCollection();
        $this->friendsWithUser = new \Doctrine\Common\Collections\ArrayCollection();
        $this->checkins = new \Doctrine\Common\Collections\ArrayCollection();
        $this->comments = new \Doctrine\Common\Collections\ArrayCollection();
        $this->toasts = new \Doctrine\Common\Collections\ArrayCollection();
        $this->saved_data = new \Doctrine\Common\Collections\ArrayCollection();
        $this->attending = new \Doctrine\Common\Collections\ArrayCollection();
    }
    
    /**
     * @JMS\VirtualProperty
     * @JMS\SerializedName("untappd_link")
     */
    public function getUntappdLink() {
        return '<a href="https://untappd.com/user/'.$this->user_name.'" target="_blank">'.$this->first_name.'</a>';
    }
    
    /**
     * @JMS\VirtualProperty
     * @JMS\SerializedName("untappd_link_full")
     */
    public function getUntappdLinkFull() {
        return '<a href="https://untappd.com/user/'.$this->user_name.'" target="_blank">'.$this->first_name.' '.$this->last_name.'</a>';
    }
    
    public function addFriend(User $friend, $date)
    {
        $fs = new Friendship();
        $fs->setUser($this);
        $fs->setFriend($friend);
        $fs->setCreatedAt($date);
        
        $this->addFriendship($fs);
    }
    
    public function addFriendship(Friendship $friendship)
    {
        $this->friends->add($friendship);
        $friendship->getFriend()->addFriendshipWithUser($friendship);
    }
    
    public function addFriendshipWithUser(Friendship $friendship)
    {
        $this->friendsWithUser->add($friendship);
    }
    
    public function resetFriends() {
        $this->friends = new \Doctrine\Common\Collections\ArrayCollection();
        return $this->friends;
    }
    
    /**
     * Set id
     *
     * @param integer $id
     *
     * @return User
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
     * Set userName
     *
     * @param string $userName
     *
     * @return User
     */
    public function setUserName($userName)
    {
        $this->user_name = $userName;

        return $this;
    }

    /**
     * Get userName
     *
     * @return string
     */
    public function getUserName()
    {
        return $this->user_name;
    }

    /**
     * Set firstName
     *
     * @param string $firstName
     *
     * @return User
     */
    public function setFirstName($firstName)
    {
        $this->first_name = $firstName;

        return $this;
    }

    /**
     * Get firstName
     *
     * @return string
     */
    public function getFirstName()
    {
        return $this->first_name;
    }

    /**
     * Set lastName
     *
     * @param string $lastName
     *
     * @return User
     */
    public function setLastName($lastName)
    {
        $this->last_name = $lastName;

        return $this;
    }

    /**
     * Get lastName
     *
     * @return string
     */
    public function getLastName()
    {
        return $this->last_name;
    }

    /**
     * Set userAvatar
     *
     * @param string $userAvatar
     *
     * @return User
     */
    public function setUserAvatar($userAvatar)
    {
        $this->user_avatar = $userAvatar;

        return $this;
    }

    /**
     * Get userAvatar
     *
     * @return string
     */
    public function getUserAvatar()
    {
        return $this->user_avatar;
    }

    /**
     * Set userAvatarHd
     *
     * @param string $userAvatarHd
     *
     * @return User
     */
    public function setUserAvatarHd($userAvatarHd)
    {
        $this->user_avatar_hd = $userAvatarHd;

        return $this;
    }

    /**
     * Get userAvatarHd
     *
     * @return string
     */
    public function getUserAvatarHd()
    {
        return $this->user_avatar_hd;
    }

    /**
     * Set userCoverPhoto
     *
     * @param string $userCoverPhoto
     *
     * @return User
     */
    public function setUserCoverPhoto($userCoverPhoto)
    {
        $this->user_cover_photo = $userCoverPhoto;

        return $this;
    }

    /**
     * Get userCoverPhoto
     *
     * @return string
     */
    public function getUserCoverPhoto()
    {
        return $this->user_cover_photo;
    }

    /**
     * Set userCoverPhotoOffset
     *
     * @param integer $userCoverPhotoOffset
     *
     * @return User
     */
    public function setUserCoverPhotoOffset($userCoverPhotoOffset)
    {
        $this->user_cover_photo_offset = $userCoverPhotoOffset;

        return $this;
    }

    /**
     * Get userCoverPhotoOffset
     *
     * @return integer
     */
    public function getUserCoverPhotoOffset()
    {
        return $this->user_cover_photo_offset;
    }

    /**
     * Set isPrivate
     *
     * @param boolean $isPrivate
     *
     * @return User
     */
    public function setIsPrivate($isPrivate)
    {
        $this->is_private = $isPrivate;

        return $this;
    }

    /**
     * Get isPrivate
     *
     * @return boolean
     */
    public function getIsPrivate()
    {
        return $this->is_private;
    }

    /**
     * Set location
     *
     * @param string $location
     *
     * @return User
     */
    public function setLocation($location)
    {
        $this->location = $location;

        return $this;
    }

    /**
     * Get location
     *
     * @return string
     */
    public function getLocation()
    {
        return $this->location;
    }

    /**
     * Set url
     *
     * @param string $url
     *
     * @return User
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
     * Set bio
     *
     * @param string $bio
     *
     * @return User
     */
    public function setBio($bio)
    {
        $this->bio = $bio;

        return $this;
    }

    /**
     * Get bio
     *
     * @return string
     */
    public function getBio()
    {
        return $this->bio;
    }

    /**
     * Set isSupporter
     *
     * @param boolean $isSupporter
     *
     * @return User
     */
    public function setIsSupporter($isSupporter)
    {
        $this->is_supporter = $isSupporter;

        return $this;
    }

    /**
     * Get isSupporter
     *
     * @return boolean
     */
    public function getIsSupporter()
    {
        return $this->is_supporter;
    }

    /**
     * Set isModerator
     *
     * @param boolean $isModerator
     *
     * @return User
     */
    public function setIsModerator($isModerator)
    {
        $this->is_moderator = $isModerator;

        return $this;
    }

    /**
     * Get isModerator
     *
     * @return boolean
     */
    public function getIsModerator()
    {
        return $this->is_moderator;
    }

    /**
     * Set untappdUrl
     *
     * @param string $untappdUrl
     *
     * @return User
     */
    public function setUntappdUrl($untappdUrl)
    {
        $this->untappd_url = $untappdUrl;

        return $this;
    }

    /**
     * Get untappdUrl
     *
     * @return string
     */
    public function getUntappdUrl()
    {
        return $this->untappd_url;
    }

    /**
     * Set accountType
     *
     * @param string $accountType
     *
     * @return User
     */
    public function setAccountType($accountType)
    {
        $this->account_type = $accountType;

        return $this;
    }

    /**
     * Get accountType
     *
     * @return string
     */
    public function getAccountType()
    {
        return $this->account_type;
    }

    /**
     * Set dateJoined
     *
     * @param \DateTime $dateJoined
     *
     * @return User
     */
    public function setDateJoined($dateJoined)
    {
        $this->date_joined = $dateJoined;

        return $this;
    }

    /**
     * Get dateJoined
     *
     * @return \DateTime
     */
    public function getDateJoined()
    {
        return $this->date_joined;
    }

    /**
     * Set internalCreatedAt
     *
     * @param \DateTime $internalCreatedAt
     *
     * @return User
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
     * @return User
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
     * @return User
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
     * Remove friend
     *
     * @param \App\Entity\User\Friendship $friend
     */
    public function removeFriend(\App\Entity\User\Friendship $friend)
    {
        $this->friends->removeElement($friend);
    }

    /**
     * Get friends
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getFriends()
    {
        return $this->friends;
    }

    /**
     * Add friendsWithUser
     *
     * @param \App\Entity\User\Friendship $friendsWithUser
     *
     * @return User
     */
    public function addFriendsWithUser(\App\Entity\User\Friendship $friendsWithUser)
    {
        $this->friendsWithUser[] = $friendsWithUser;

        return $this;
    }

    /**
     * Remove friendsWithUser
     *
     * @param \App\Entity\User\Friendship $friendsWithUser
     */
    public function removeFriendsWithUser(\App\Entity\User\Friendship $friendsWithUser)
    {
        $this->friendsWithUser->removeElement($friendsWithUser);
    }

    /**
     * Get friendsWithUser
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getFriendsWithUser()
    {
        return $this->friendsWithUser;
    }

    /**
     * Add checkin
     *
     * @param \App\Entity\Checkin\Checkin $checkin
     *
     * @return User
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
     * Add comment
     *
     * @param \App\Entity\Checkin\Comment $comment
     *
     * @return User
     */
    public function addComment(\App\Entity\Checkin\Comment $comment)
    {
        $this->comments[] = $comment;

        return $this;
    }

    /**
     * Remove comment
     *
     * @param \App\Entity\Checkin\Comment $comment
     */
    public function removeComment(\App\Entity\Checkin\Comment $comment)
    {
        $this->comments->removeElement($comment);
    }

    /**
     * Get comments
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getComments()
    {
        return $this->comments;
    }

    /**
     * Add toast
     *
     * @param \App\Entity\Checkin\Toast $toast
     *
     * @return User
     */
    public function addToast(\App\Entity\Checkin\Toast $toast)
    {
        $this->toasts[] = $toast;

        return $this;
    }

    /**
     * Remove toast
     *
     * @param \App\Entity\Checkin\Toast $toast
     */
    public function removeToast(\App\Entity\Checkin\Toast $toast)
    {
        $this->toasts->removeElement($toast);
    }

    /**
     * Get toasts
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getToasts()
    {
        return $this->toasts;
    }
    
    /**
     * Set totalBadges
     *
     * @param integer $totalBadges
     *
     * @return User
     */
    public function setTotalBadges($totalBadges)
    {
        $this->total_badges = $totalBadges;
        
        return $this;
    }
    
    /**
     * Get totalBadges
     *
     * @return integer
     */
    public function getTotalBadges()
    {
        return $this->total_badges;
    }
    
    /**
     * Set totalFriends
     *
     * @param integer $totalFriends
     *
     * @return User
     */
    public function setTotalFriends($totalFriends)
    {
        $this->total_friends = $totalFriends;
        
        return $this;
    }
    
    /**
     * Get totalFriends
     *
     * @return integer
     */
    public function getTotalFriends()
    {
        return $this->total_friends;
    }
    
    /**
     * Set totalCheckins
     *
     * @param integer $totalCheckins
     *
     * @return User
     */
    public function setTotalCheckins($totalCheckins)
    {
        $this->total_checkins = $totalCheckins;
        
        return $this;
    }
    
    /**
     * Get totalCheckins
     *
     * @return integer
     */
    public function getTotalCheckins()
    {
        return $this->total_checkins;
    }
    
    /**
     * Set totalBeers
     *
     * @param integer $totalBeers
     *
     * @return User
     */
    public function setTotalBeers($totalBeers)
    {
        $this->total_beers = $totalBeers;
        
        return $this;
    }
    
    /**
     * Get totalBeers
     *
     * @return integer
     */
    public function getTotalBeers()
    {
        return $this->total_beers;
    }
    
    /**
     * Set totalCreatedBeers
     *
     * @param integer $totalCreatedBeers
     *
     * @return User
     */
    public function setTotalCreatedBeers($totalCreatedBeers)
    {
        $this->total_created_beers = $totalCreatedBeers;
        
        return $this;
    }
    
    /**
     * Get totalCreatedBeers
     *
     * @return integer
     */
    public function getTotalCreatedBeers()
    {
        return $this->total_created_beers;
    }
    
    /**
     * Set totalFollowings
     *
     * @param integer $totalFollowings
     *
     * @return User
     */
    public function setTotalFollowings($totalFollowings)
    {
        $this->total_followings = $totalFollowings;
        
        return $this;
    }
    
    /**
     * Get totalFollowings
     *
     * @return integer
     */
    public function getTotalFollowings()
    {
        return $this->total_followings;
    }
    
    /**
     * Set totalPhotos
     *
     * @param integer $totalPhotos
     *
     * @return User
     */
    public function setTotalPhotos($totalPhotos)
    {
        $this->total_photos = $totalPhotos;
        
        return $this;
    }
    
    /**
     * Get totalPhotos
     *
     * @return integer
     */
    public function getTotalPhotos()
    {
        return $this->total_photos;
    }
    
    /**
     * Set foursquare
     *
     * @param integer $foursquare
     *
     * @return User
     */
    public function setFoursquare($foursquare)
    {
        $this->foursquare = $foursquare;
        
        return $this;
    }
    
    /**
     * Get foursquare
     *
     * @return integer
     */
    public function getFoursquare()
    {
        return $this->foursquare;
    }
    
    /**
     * Set twitter
     *
     * @param string $twitter
     *
     * @return User
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
     * @return User
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
    
    /**
     * @return mixed
     */
    public function getInternalFriendlistLastOffset()
    {
        return $this->internal_friendlist_last_offset;
    }
    
    
    /**
     * @param mixed $internalFriendlistLastOffset
     */
    public function setInternalFriendlistLastOffset($internalFriendlistLastOffset)
    {
        $this->internal_friendlist_last_offset = $internalFriendlistLastOffset;
        
        return $this;
    }
    
    /**
     * Set internalUntappdAccessToken
     *
     * @param string $internalUntappdAccessToken
     *
     * @return User
     */
    public function setInternalUntappdAccessToken($internalUntappdAccessToken)
    {
        $this->internal_untappd_access_token = $internalUntappdAccessToken;
        
        return $this;
    }
    
    /**
     * Get twitter
     *
     * @return string
     */
    public function getInternalUntappdAccessToken()
    {
        return $this->internal_untappd_access_token;
    }
    
    /**
     * Add badgeRelation
     *
     * @param \App\Entity\Badge\BadgeRelation $badgeRelation
     *
     * @return User
     */
    public function addBadgeRelation(\App\Entity\Badge\BadgeRelation $badgeRelation)
    {
        $this->badge_relation[] = $badgeRelation;
        
        return $this;
    }
    
    /**
     * Remove badgeRelation
     *
     * @param \App\Entity\Badge\BadgeRelation $badgeRelation
     */
    public function removeBadgeRelation(\App\Entity\Badge\Badge $badgeRelation)
    {
        $this->badge_relation->removeElement($badgeRelation);
    }
    
    /**
     * Get badgeRelation
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getBadgeRelations()
    {
        return $this->badge_relation;
    }
    
    /**
     * Add saved_data
     *
     * @param \App\Entity\User\SavedData $savedData
     *
     * @return User
     */
    public function addSavedData(\App\Entity\User\SavedData $savedData)
    {
        $this->saved_data[] = $savedData;
        
        return $this;
    }
    
    /**
     * Remove saved_data
     *
     * @param \App\Entity\User\SavedData $savedData
     */
    public function removeSavedData(\App\Entity\User\SavedData $savedData)
    {
        $this->saved_data->removeElement($savedData);
    }
    
    /**
     * Get saved_data
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getSavedData()
    {
        return $this->saved_data;
    }
    
    /**
     * Get $internal_latest_checkin_refresh
     *
     * @return mixed
     */
    public function getInternalLatestCheckinRefresh()
    {
        return $this->internal_latest_checkin_refresh;
    }
    
    /**
     * Set $internal_latest_checkin_refresh
     *
     * @param mixed $internal_latest_checkin_refresh
     *
     * @return User
     */
    public function setInternalLatestCheckinRefresh($internal_latest_checkin_refresh)
    {
        $this->internal_latest_checkin_refresh = $internal_latest_checkin_refresh;
        
        return $this;
    }
    
    /**
     * Add attending
     *
     * @param \App\Entity\Event\Event $event
     *
     * @return User
     */
    public function addAttending(\App\Entity\Event\Event $event)
    {
        $event->addUserAttending($this);
        $this->attending[] = $event;
        
        return $this;
    }
    
    /**
     * Remove attending
     *
     * @param \App\Entity\Event\Event $event
     */
    public function removeAttending(\App\Entity\Event\Event $event)
    {
        $event->removeUserAttending($this);
        $this->attending->removeElement($event);
    }
    
    /**
     * Get attending
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getAttending()
    {
        return $this->attending;
    }
    
}
