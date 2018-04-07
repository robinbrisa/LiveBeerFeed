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

class EventStats
{
    private $em;
    
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }
    
    public function returnRandomStatistic($event) {
        $availableStatistics = array(
          /*
            'get_most_checked_in_beer',
            'get_most_checked_in_brewery',
            'get_most_checked_in_style',
            'get_checkin_with_most_badges',
            'get_ratings_average', 
            'get_best_rated_brewery',
          */
            'most_no_rating_checkins'
        );
        
        // 'get_best_rated_beer',
        // 'get_best_rated_style',
        // 'style_with_most_beers',
        // 'brewery_with_most_beers',
        // 'user_with_most_checkins',
        // 'checkin_with_most_toasts'
        
        $rand = rand(0, count($availableStatistics) - 1);
        $selectedStatistic = $availableStatistics[$rand];
        return $this->$selectedStatistic($event);
    }
    
    private function get_best_rated_beer($event) {
        $output = array(
            'line1' => '<b>Best Rated Beer</b>',
            'line2' => '',
            'line3' => ''
        );
        return $output;
    }
    
    private function get_best_rated_brewery($event) {
        $minRatings = 5;
        if ($results = $this->em->getRepository('\App\Entity\Brewery\Brewery')->getBestRatedBrewery(null, $event->getVenues(), $event->getStartDate(), null, $minRatings)) {
            $output = array(
                'line1' => '<span class="info-major">Best Rated Brewery</span>',
                'line2' => '<div class="avatar-wrapper"><img src="' . $results[0]->getLabel() . '" /></div> ' . $results[0]->getName(),
                'line3' => round($results['avg_rating'], 2) . '/5 (' . $results['total'] . ' check-ins)'
            );
            return $output;
        } else {
            return false;
        }
    }
    
    private function get_best_rated_style($event) {
        $output = array(
            'line1' => 'Best Rated Style',
            'line2' => '',
            'line3' => ''
        );
        return $output;
    }
    
    private function get_most_checked_in_beer($event) {
        if ($results = $this->em->getRepository('\App\Entity\Beer\Beer')->getMostCheckedInBeer(null, $event->getVenues(), $event->getStartDate())) {
            $output = array(
                'line1' => '<span class="info-major">Most Checked-in Beer</span>',
                'line2' => '<div class="avatar-wrapper"><img src="' . $results[0]->getLabel() . '" /></div> <b>' . $results[0]->getName() . '</b> (' . $results[0]->getBrewery()->getName() . ')',
                'line3' => $results['total'] . ' check-ins'
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
                'line2' => $results[0]->getName(),
                'line3' => $results['total'] . ' check-ins'
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
                'line3' => $results['total'] . ' check-ins'
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
                'line2' => round($results['average'], 2) . '/5',
                'line3' => $results['total'] . ' check-ins with ratings'
            );
            return $output;
        } else {
            return false;
        }
    }
    
    private function get_checkin_with_most_badges($event) {
        if ($results = $this->em->getRepository('\App\Entity\Checkin\Checkin')->getCheckinWithMostBadges(null, $event->getVenues(), $event->getStartDate())) {
            $badgeIcons = '';
            foreach($results->getBadgeRelations() as $badge) {
                $badgeIcons .= '<img src="' . $badge->getBadge()->getBadgeImageSm() . '"/> ';
            }
            $output = array(
                'line1' => '<span class="info-major">Check-in With Most Badge Unlocks</span>',
                'line2' => $results->getTotalBadges() . ' badges unlocked ' . $badgeIcons,
                'line3' => '<div class="avatar-wrapper"><img src="' . $results->getUser()->getUserAvatar() . '"/></div> ' . $results->getUser()->getFirstName() . ' ' . $results->getUser()->getLastName() . ' <div class="avatar-wrapper"><img src="' . $results->getBeer()->getLabel() . '" /></div> ' . $results->getBeer()->getName()
            );
            return $output;
        } else {
            return false;
        }        
    }
    
    private function most_no_rating_checkins($event) {
        if ($results = $this->em->getRepository('\App\Entity\Checkin\Checkin')->getNoRatingCheckinsCount(null, $event->getVenues(), $event->getStartDate())) {
            $output = array(
                'line1' => '<span class="info-major">Check-ins With No Ratings</span>',
                'line2' => $results['total'] . ' check-ins with no ratings',
                'line3' => ''
            );
            return $output;
        } else {
            return false;
        }        
        
    }
}