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
        $this->em->getConnection()->getConfiguration()->setSQLLogger(null);
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
    
    public function getAPIKeysPool() {
        $usedKeys = $this->em->getRepository('App\Entity\APIQueryLog')->findUsedAPIKeys();
        $userKeys = $this->em->getRepository('App\Entity\User\User')->getAPIKeys();
        $finalPool = array_merge($userKeys, $usedKeys);
        arsort($finalPool);
        unset($usedKeys);
        unset($userKeys);
        return $finalPool;
    }
    
    public function getBestAPIKey($keyPool) {
        if ($keyPool['default'] > 1) {
            $APIToken = null;
        } else {
            unset($keyPool['default']);
            while (current($keyPool) < 1 && current($keyPool) !== false) {
                next($keyPool);
                if (current($keyPool) === false) {
                    return false;
                }
            }
            $APIToken = key($keyPool);
        }
        return $APIToken;
    }
}