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
}
