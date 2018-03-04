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
     * @ORM\ManyToOne(targetEntity="Checkin", inversedBy="comments", cascade={"remove"})
     * @ORM\JoinColumn(name="checkin_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $checkin;
    
    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User\User", inversedBy="comments", cascade={"persist"})
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

    /**
     * Set id
     *
     * @param integer $id
     *
     * @return Comment
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
     * Set comment
     *
     * @param string $comment
     *
     * @return Comment
     */
    public function setComment($comment)
    {
        $this->comment = $comment;

        return $this;
    }

    /**
     * Get comment
     *
     * @return string
     */
    public function getComment()
    {
        return $this->comment;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     *
     * @return Comment
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
     * Set commentSource
     *
     * @param string $commentSource
     *
     * @return Comment
     */
    public function setCommentSource($commentSource)
    {
        $this->comment_source = $commentSource;

        return $this;
    }

    /**
     * Get commentSource
     *
     * @return string
     */
    public function getCommentSource()
    {
        return $this->comment_source;
    }

    /**
     * Set checkin
     *
     * @param \App\Entity\Checkin\Checkin $checkin
     *
     * @return Comment
     */
    public function setCheckin(\App\Entity\Checkin\Checkin $checkin = null)
    {
        $this->checkin = $checkin;

        return $this;
    }

    /**
     * Get checkin
     *
     * @return \App\Entity\Checkin\Checkin
     */
    public function getCheckin()
    {
        return $this->checkin;
    }

    /**
     * Set user
     *
     * @param \App\Entity\User\User $user
     *
     * @return Comment
     */
    public function setUser(\App\Entity\User\User $user = null)
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
}
