<?php
// src/Service/EventStats.php
namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;
use App\Entity\User\User as User;
use App\Entity\Checkin\Checkin as Checkin;
use App\Entity\Brewery\Brewery as Brewery;
use App\Entity\Venue\Venue as Venue;
use App\Entity\Beer\Beer as Beer;
use App\Entity\Beer\Style as Style;
use App\Service\Tools;

class EventStats
{
    private $em;
    
    public function __construct(EntityManagerInterface $em, Tools $tools)
    {
        $this->em = $em;
        $this->tools = $tools;
    }
    
    public function returnRandomStatistic($event) {
        $availableStatistics = array(
      /*    
            'get_most_checked_in_style',
            'get_most_checked_in_brewery',
            'get_best_rated_brewery',
            'get_most_checked_in_beer',
            'get_ratings_average', 
            'get_most_no_rating_checkins',
            'get_most_active_user_last_hour',
            'get_most_active_user_four_hours',
            'get_best_rated_beer',
            'get_best_rated_style', */
            'get_checkin_with_most_badges',
        );
        
        // 'get_checkins_per_hour',
        // 'style_with_most_beers',
        // 'brewery_with_most_beers',
        // 'user_with_most_checkins',
        
        $i = 0;
        $rand = rand(0, count($availableStatistics) - 1);
        $selectedStatistic = $availableStatistics[$rand];
        $output = false;
        // Make 3 tries before giving up
        while (!$output && $i < 3) {
            $output = $this->$selectedStatistic($event);
            if (!$output) {
                $availableStatistics = array_diff($availableStatistics, array($selectedStatistic));
                if (count($availableStatistics) <= 0) {
                    break;
                }
                $rand = rand(0, count($availableStatistics) - 1);
                $selectedStatistic = $availableStatistics[$rand];
                $i++;
            }
        }
        return $output;
    }
    
    private function get_best_rated_beer($event) {
        $minRatings = 4;
        if ($results = $this->em->getRepository('\App\Entity\Beer\Beer')->getBestRatedBeer(null, $event->getVenues(), $event->getStartDate(), null, $minRatings)) {
            $output = array(
                'line1' => '<span class="info-major">Best Rated Beer</span>',
                'line2' => $this->returnBeerWithLabel($results[0], true) . ' (' . $results[0]->getBrewery()->getName() . ')',
                'line3' => $this->tools->getRatingImage($results['avg_rating']) . ' (' . round($results['avg_rating'], 2) . '/5, <span class="animated-increment" data-value="' . $results['total'] . '">0</span> check-ins)'
            );
            return $output;
        } else {
            return false;
        }
    }
    
    private function get_best_rated_brewery($event) {
        $minRatings = 8;
        if ($results = $this->em->getRepository('\App\Entity\Brewery\Brewery')->getBestRatedBrewery(null, $event->getVenues(), $event->getStartDate(), null, $minRatings)) {
            $output = array(
                'line1' => '<span class="info-major">Best Rated Brewery</span>',
                'line2' => $this->returnBreweryWithLabel($results[0], true),
                'line3' => $this->tools->getRatingImage($results['avg_rating']) . ' (' . round($results['avg_rating'], 2) . '/5, <span class="animated-increment" data-value="' . $results['total'] . '">0</span> check-ins)'
            );
            return $output;
        } else {
            return false;
        }
    }
    
    private function get_best_rated_style($event) {
        $minRatings = 4;
        if ($results = $this->em->getRepository('\App\Entity\Beer\Style')->getBestRatedStyle(null, $event->getVenues(), $event->getStartDate(), null, $minRatings)) {
            $output = array(
                'line1' => '<span class="info-major">Best Rated Style</span>',
                'line2' => $results[0]->getName(),
                'line3' => $this->tools->getRatingImage($results['avg_rating']) . ' (' . round($results['avg_rating'], 2) . '/5, <span class="animated-increment" data-value="' . $results['total'] . '">0</span> check-ins)'
            );
            return $output;
        } else {
            return false;
        }
    }
    
    private function get_most_checked_in_beer($event) {
        if ($results = $this->em->getRepository('\App\Entity\Beer\Beer')->getMostCheckedInBeer(null, $event->getVenues(), $event->getStartDate())) {
            $output = array(
                'line1' => '<span class="info-major">Most Checked-in Beer</span>',
                'line2' => $this->returnBeerWithLabel($results[0], true) . ' (' . $results[0]->getBrewery()->getName() . ')',
                'line3' => '<span class="animated-increment" data-value="' . $results['total'] . '">0</span> check-ins'
            );
            return $output;
        } else {
            return false;
        }
    }
    
    private function get_most_checked_in_brewery($event) {
        if ($results = $this->em->getRepository('\App\Entity\Brewery\Brewery')->getMostCheckedInBrewery(null, $event->getVenues(), $event->getStartDate())) {
            $output = array(
                'line1' => '<span class="info-major">Most Checked-in Brewery</span>',
                'line2' => $this->returnBreweryWithLabel($results[0], true),
                'line3' => '<span class="animated-increment" data-value="' . $results['total'] . '">0</span> check-ins'
            );
            return $output;
        } else {
            return false;
        }
    }
    
    private function get_most_checked_in_style($event) {
        if ($results = $this->em->getRepository('\App\Entity\Beer\Style')->getMostCheckedInStyle(null, $event->getVenues(), $event->getStartDate())) {
            $output = array(
                'line1' => '<span class="info-major">Most Checked-in Style</span>',
                'line2' => $results[0]->getName(),
                'line3' => '<span class="animated-increment" data-value="' . $results['total'] . '">0</span> check-ins'
            );
            return $output;
        } else {
            return false;
        }
    }
    
    private function get_ratings_average($event) {
        if ($results = $this->em->getRepository('\App\Entity\Checkin\Checkin')->getAverageRatingByCheckin(null, $event->getVenues(), $event->getStartDate())) {
            $output = array(
                'line1' => '<span class="info-major">Ratings Average</span>',
                'line2' => $this->tools->getRatingImage($results['average']) . ' (' . round($results['average'], 2) . '/5)',
                'line3' => '<span class="animated-increment" data-value="' . $results['total'] . '">0</span> check-ins with ratings'
            );
            return $output;
        } else {
            return false;
        }
    }
    
    private function get_checkin_with_most_badges($event) {
        $minBadges = 2;
        $results = $this->em->getRepository('\App\Entity\Checkin\Checkin')->getCheckinWithMostBadges(null, $event->getVenues(), $event->getStartDate());
        if ($results && count($results->getBadgeRelations()) >= $minBadges) {
            $badgeIcons = '';
            foreach($results->getBadgeRelations() as $badge) {
                $badgeIcons .= '<img src="' . $badge->getBadge()->getBadgeImageSm() . '"/> ';
            }
            $output = array(
                'line1' => '<span class="info-major">Check-in With Most Badge Unlocks</span>',
                'line2' => '<span class="animated-increment" data-value="' . $results->getTotalBadges() . '">0</span> badges unlocked ' . $badgeIcons,
                'line3' => $this->returnUserWithAvatar($results->getUser()) . ' ' . $this->returnBeerWithLabel($results->getBeer())
            );
            return $output;
        } else {
            return false;
        }        
    }
    
    private function get_most_no_rating_checkins($event) {
        $results = $this->em->getRepository('\App\Entity\User\User')->getNoRatingCheckinsCount(null, $event->getVenues(), $event->getStartDate());
        // Minimum 3 check-ins to be relevant
        if ($results && $results['total'] > 2) {
            $total = $this->em->getRepository('\App\Entity\Checkin\Checkin')->getNoRatingCheckinsCount(null, $event->getVenues(), $event->getStartDate());
            $output = array(
                'line1' => '<span class="info-major">The Indecisive Award</span>',
                'line2' => $this->returnUserWithAvatar($results[0], true) . ' has made <b><span class="animated-increment" data-value="' . $results['total'] . '">0</span></b> check-ins with no rating',
                'line3' => 'out of <b><span class="animated-increment" data-value="' . $total['total'] . '">0</span></b> total check-ins with no rating'
            );
            return $output;
        } else {
            return false;
        }
    }
    
    private function get_most_active_user_last_hour($event) {
        $results = $this->em->getRepository('\App\Entity\User\User')->getMostCheckinsCount(null, $event->getVenues(), new \DateTime('- 1 hour'));
        // Minimum 2 check-ins to be relevant
        if ($results && $results['total'] > 1) {
            $total = $this->em->getRepository('\App\Entity\Checkin\Checkin')->getTotalCheckinsCount(null, $event->getVenues(), new \DateTime('- 1 hour'));
            $output = array(
                'line1' => '<span class="info-major">Most Active (Last Hour)</span>',
                'line2' => $this->returnUserWithAvatar($results[0], true) . ' has made <b><span class="animated-increment" data-value="' . $results['total'] . '">0</span></b> check-ins',
                'line3' => 'out of <b><span class="animated-increment" data-value="' . $total . '">0</span></b> total check-ins'
            );
            return $output;
        } else {
            return false;
        }
    }
    
    private function get_most_active_user_four_hours($event) {
        $results = $this->em->getRepository('\App\Entity\User\User')->getMostCheckinsCount(null, $event->getVenues(), new \DateTime('- 4 hour'));
        // Minimum 5 check-ins to be relevant
        if ($results && $results['total'] > 4) {
            $total = $this->em->getRepository('\App\Entity\Checkin\Checkin')->getTotalCheckinsCount(null, $event->getVenues(), new \DateTime('- 4 hour'));
            $output = array(
                'line1' => '<span class="info-major">Most Active (Last 4 Hours)</span>',
                'line2' => $this->returnUserWithAvatar($results[0], true) . ' has made <b><span class="animated-increment" data-value="' . $results['total'] . '">0</span></b> check-ins',
                'line3' => 'out of <b><span class="animated-increment" data-value="' . $total . '">0</span></b> total check-ins'
            );
            return $output;
        } else {
            return false;
        }
    }
    
    private function returnUserWithAvatar($user, $bold = false) {
        $output = '<div class="image-wrapper"><img src="' . $user->getUserAvatar() . '"/></div> ';
        if ($bold) { $output .= '<b>'; }
        $output .= $user->getFirstName() . ' ' . $user->getLastName();
        if ($bold) { $output .= '</b>'; }
        return $output;
    }
    
    private function returnBeerWithLabel($beer, $bold = false) {
        $output = '<div class="image-wrapper"><img src="' . $beer->getLabel() . '"/></div> ';
        if ($bold) { $output .= '<b>'; }
        $output .= $beer->getName();
        if ($bold) { $output .= '</b>'; }
        return $output;
    }
    
    private function returnBreweryWithLabel($brewery, $bold = false) {
        $output = '<div class="image-wrapper"><img src="' . $brewery->getLabel() . '"/></div> ';
        if ($bold) { $output .= '<b>'; }
        $output .= $brewery->getName();
        if ($bold) { $output .= '</b>'; }
        return $output;
    }
}