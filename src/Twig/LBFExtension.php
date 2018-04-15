<?php
// src/Twig/LBFExtension.php
namespace App\Twig;

use App\Twig\AppRuntime;

class LBFExtension extends \Twig_Extension
{
    public function getFilters()
    {
        return array(
            // the logic of this filter is now implemented in a different class
            new \Twig_SimpleFilter('rating', array(AppRuntime::class, 'ratingFilter')),
        );
    }
}