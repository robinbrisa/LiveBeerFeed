<?php

namespace App\Entity\User;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

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
     * @ORM\Column(type="string")
     */
    private $user_avatar_hd;
    
    /**
     * @ORM\Column(type="string")
     */
    private $user_cover_photo;
    
    /**
     * @ORM\Column(type="integer")
     */
    private $user_cover_photo_offset;
    
    /**
     * @ORM\Column(type="boolean")
     */
    private $is_private;
    
    /**
     * @ORM\Column(type="string")
     */
    private $location;
    
    /**
     * @ORM\Column(type="string")
     */
    private $url;
    
    /**
     * @ORM\Column(type="text")
     */
    private $bio;
    
    /**
     * @ORM\Column(type="boolean")
     */
    private $is_supporter;
    
    /**
     * @ORM\Column(type="boolean")
     */
    private $is_moderator;
    
    /**
     * @ORM\Column(type="string")
     */
    private $untappd_url;
    
    /**
     * @ORM\Column(type="string")
     */
    private $account_type;
    
    /**
     * @ORM\OneToOne(targetEntity="Stats")
     * @ORM\JoinColumn(name="stats_id", referencedColumnName="id")
     */
    private $stats;
    
    /**
     * @ORM\OneToOne(targetEntity="Contact")
     * @ORM\JoinColumn(name="contact_id", referencedColumnName="id")
     */
    private $contact;
    
    /**
     * @ORM\Column(type="datetime")
     */
    private $date_joined;
    
    /**
     * @ORM\OneToMany(targetEntity="Friendship", mappedBy="user")
     */
    private $friends;
    
    /**
     * @ORM\OneToMany(targetEntity="Friendship", mappedBy="friend")
     */
    private $friendsWithUser;
    
    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Checkin\Checkin", mappedBy="user")
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
        $friendship->friend->addFriendshipWithUser($friendship);
    }
    
    public function addFriendshipWithUser(Friendship $friendship)
    {
        $this->friendsWithUser->add($friendship);
    }
    
}

