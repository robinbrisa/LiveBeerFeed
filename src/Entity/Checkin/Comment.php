<?php

namespace App\Entity\Checkin;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="checkin_comment")
 * @ORM\Entity(repositoryClass="App\Repository\Checkin\CommentRepository")
 */
class Comment
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     */
    private $id;
    
    /**
     * @ORM\ManyToOne(targetEntity="Checkin", inversedBy="comments")
     * @ORM\JoinColumn(name="checkin_id", referencedColumnName="id")
     */
    private $checkin;
    
    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User\User", inversedBy="comments")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     */
    private $user;
    
    /**
     * @ORM\Column(type="string")
     */
    private $comment;
    
    /**
     * @ORM\Column(type="datetime")
     */
    private $created_at;
    
    /**
     * @ORM\Column(type="string")
     */
    private $comment_source;
}
