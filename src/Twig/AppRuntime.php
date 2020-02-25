<?php 
// src/Twig/AppRuntime.php
namespace App\Twig;

use App\Service\Tools;

class AppRuntime
{
    public function __construct(Tools $tools)
    {
        $this->tools = $tools;
    }

    public function ratingFilter($number)
    {
        return $this->tools->getRatingBar($number);
    }
        
    public function untappdRatingFilter($number)
    {
        return $this->tools->getRatingImage($number);
    }
    
    public function countryFilter($country)
    {
        return $this->tools->countryCode($country);
    }
}