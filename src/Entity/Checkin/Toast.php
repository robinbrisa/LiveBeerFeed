<?php

namespace App\Entity\Checkin;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="checkin_toast")
 * @ORM\Entity(repositoryClass="App\Repository\Checkin\ToastRepository")
 */
class Toast
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;
    
    /**
     * @ORM\ManyToOne(targetEntity="Checkin", inversedBy="toasts")
     * @ORM\JoinColumn(name="checkin_id", referencedColumnName="id")
     */
    private $checkin;
    
    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User\User", inversedBy="toasts")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     */
    private $user;
        
    /**
     * @ORM\Column(type="datetime")
     */
    private $created_at;
}
