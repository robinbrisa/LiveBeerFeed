<?php
// src/Service/Tools.php
namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;
use App\Entity\User\User as User;
use App\Entity\Checkin\Checkin as Checkin;
use App\Entity\Brewery\Brewery as Brewery;
use App\Entity\Venue\Venue as Venue;
use App\Entity\Beer\Beer as Beer;
use App\Entity\Beer\Style as Style;

class Tools
{
    private $em;
    
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }
    
    public function getRatingImage($rating) {
        $rating = round(round($rating * 4) / 4, 2);
        if ($rating < 0.25) {
            $rating = 0.25;
        }
        if ($rating > 5) {
            $rating = 5;
        }
        $rating = $rating * 100;
        return '<span class="rating small r' . $rating . '"></span>';
    }
}