<?php

namespace App\Entity\User;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="user_friendship")
 * @ORM\Entity(repositoryClass="App\Repository\User\FriendhipRepository")
 */
class Friendship
{
    /**
     * @ORM\ManyToOne(targetEntity="User", inversedBy="friends")
     * @ORM\Id
     */
    private $user;
    
    /**
     * @ORM\ManyToOne(targetEntity="User", inversedBy="friendsWithUser")
     * @ORM\Id
     */
    private $friend;
    
    /**
     * @ORM\Column(type="datetime")
     */
    private $created_at;

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     *
     * @return Friendship
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
     * Set user
     *
     * @param \App\Entity\User\User $user
     *
     * @return Friendship
     */
    public function setUser(\App\Entity\User\User $user)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return \App\Entity\User\User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set friend
     *
     * @param \App\Entity\User\User $friend
     *
     * @return Friendship
     */
    public function setFriend(\App\Entity\User\User $friend)
    {
        $this->friend = $friend;

        return $this;
    }

    /**
     * Get friend
     *
     * @return \App\Entity\User\User
     */
    public function getFriend()
    {
        return $this->friend;
    }
}
