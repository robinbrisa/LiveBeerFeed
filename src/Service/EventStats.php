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
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Translation\Loader\XliffFileLoader;

class EventStats
{
    private $em;
    
    public function __construct(EntityManagerInterface $em, Tools $tools, TranslatorInterface $translator)
    {
        $this->em = $em;
        $this->tools = $tools;
        $this->translator = $translator;
        
        $this->availableStatistics = array(
             'get_most_checked_in_style',
             'get_most_checked_in_brewery',
             'get_most_checked_in_beer',
             'get_best_rated_brewery',
             'get_best_rated_style',
             'get_best_rated_beer',
             'get_most_active_user_last_hour',
             'get_most_active_user_four_hours',
             'get_most_active_user_today',
             'get_most_active_user_whole_event',
             'get_unique_users_count_today',
             'get_unique_beers_count',
             'get_most_no_rating_checkins',
             'get_checkin_with_most_badges',
             'get_style_with_most_beers',
             'get_ratings_average',
        );
        // get_coming_back_users
        
    }
    
    public function returnRandomStatistic($event) {
        $i = 0;
        $availableStatistics = $this->availableStatistics;
        $rand = array_rand($availableStatistics);
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
                $rand = array_rand($availableStatistics);
                $selectedStatistic = $availableStatistics[$rand];
                $i++;
            }
        }
        return $output;
    }
    
    public function debugStatistics($event) {
        $output = array();
        foreach($this->availableStatistics as $function) {
            $output[$function] = $this->$function($event);
        }
        return $output;
    }
    
    private function get_best_rated_beer($event) {
        $minRatings = 4;
        if ($results = $this->em->getRepository('\App\Entity\Beer\Beer')->getBestRatedBeer(null, $event->getVenues(), $event->getStartDate(), null, $minRatings)) {
            $output = array(
                'line1' => '<span class="info-major">' . $this->translator->trans('stats.beer_rated_beer.title') . '</span>',
                'line2' => $this->returnBeerWithLabel($results[0], true) . ' (' . $results[0]->getBrewery()->getName() . ')',
                'line3' => $this->tools->getRatingImage($results['avg_rating']) . ' (' . round($results['avg_rating'], 2) . '/5, <span class="animated-increment" data-value="' . $results['total'] . '">0</span> ' . $this->translator->trans('stats.general.checkins') . ')'
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
                'line1' => '<span class="info-major">' . $this->translator->trans('stats.beer_rated_brewery.title') . '</span>',
                'line2' => $this->returnBreweryWithLabel($results[0], true),
                'line3' => $this->tools->getRatingImage($results['avg_rating']) . ' (' . round($results['avg_rating'], 2) . '/5, <span class="animated-increment" data-value="' . $results['total'] . '">0</span> ' . $this->translator->trans('stats.general.checkins') . ')'
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
                'line1' => '<span class="info-major">' . $this->translator->trans('stats.beer_rated_style.title') . '</span>',
                'line2' => $results[0]->getName(),
                'line3' => $this->tools->getRatingImage($results['avg_rating']) . ' (' . round($results['avg_rating'], 2) . '/5, <span class="animated-increment" data-value="' . $results['total'] . '">0</span> ' . $this->translator->trans('stats.general.checkins') . ')'
            );
            return $output;
        } else {
            return false;
        }
    }
    
    private function get_most_checked_in_beer($event) {
        if ($results = $this->em->getRepository('\App\Entity\Beer\Beer')->getMostCheckedInBeer(null, $event->getVenues(), $event->getStartDate())) {
            $output = array(
                'line1' => '<span class="info-major">' . $this->translator->trans('stats.most_checked_in_beer.title') . '</span>',
                'line2' => $this->returnBeerWithLabel($results[0], true) . ' (' . $results[0]->getBrewery()->getName() . ')',
                'line3' => '<span class="animated-increment" data-value="' . $results['total'] . '">0</span> ' . $this->translator->trans('stats.general.checkins')
            );
            return $output;
        } else {
            return false;
        }
    }
    
    private function get_most_checked_in_brewery($event) {
        if ($results = $this->em->getRepository('\App\Entity\Brewery\Brewery')->getMostCheckedInBrewery(null, $event->getVenues(), $event->getStartDate())) {
            $output = array(
                'line1' => '<span class="info-major">' . $this->translator->trans('stats.most_checked_in_brewery.title') . '</span>',
                'line2' => $this->returnBreweryWithLabel($results[0], true),
                'line3' => '<span class="animated-increment" data-value="' . $results['total'] . '">0</span> ' . $this->translator->trans('stats.general.checkins')
            );
            return $output;
        } else {
            return false;
        }
    }
    
    private function get_most_checked_in_style($event) {
        if ($results = $this->em->getRepository('\App\Entity\Beer\Style')->getMostCheckedInStyle(null, $event->getVenues(), $event->getStartDate())) {
            $output = array(
                'line1' => '<span class="info-major">' . $this->translator->trans('stats.most_checked_in_style.title') . '</span>',
                'line2' => $results[0]->getName(),
                'line3' => '<span class="animated-increment" data-value="' . $results['total'] . '">0</span> ' . $this->translator->trans('stats.general.checkins')
            );
            return $output;
        } else {
            return false;
        }
    }
    
    private function get_ratings_average($event) {
        $results = $this->em->getRepository('\App\Entity\Checkin\Checkin')->getAverageRatingByCheckin(null, $event->getVenues(), $event->getStartDate());
        if ($results['total'] > 0) {
            $output = array(
                'line1' => '<span class="info-major">' . $this->translator->trans('stats.ratings_average.title') . '</span>',
                'line2' => $this->tools->getRatingImage($results['average']) . ' (' . round($results['average'], 2) . '/5)',
                'line3' => '<b><span class="animated-increment" data-value="' . $results['total'] . '">0</span></b> ' . $this->translator->trans('stats.ratings_average.total')
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
                'line1' => '<span class="info-major">' . $this->translator->trans('stats.most_badges.title') . '</span>',
                'line2' => '<b><span class="animated-increment" data-value="' . $results->getTotalBadges() . '">0</span></b> ' . $this->translator->trans('stats.most_badges.badges_unlocked') . ' ' . $badgeIcons,
                'line3' => $this->returnUserWithAvatar($results->getUser()) . ' ' . $this->returnBeerWithLabel($results->getBeer())
            );
            return $output;
        } else {
            return false;
        }        
    }
    
    private function get_most_no_rating_checkins($event) {
        $minCheckins = 3;
        $results = $this->em->getRepository('\App\Entity\User\User')->getNoRatingCheckinsCount(null, $event->getVenues(), $event->getStartDate());
        // Minimum 3 check-ins to be relevant
        if ($results && $results['total'] >= $minCheckins) {
            $total = $this->em->getRepository('\App\Entity\Checkin\Checkin')->getNoRatingCheckinsCount(null, $event->getVenues(), $event->getStartDate());
            $output = array(
                'line1' => '<span class="info-major">' . $this->translator->trans('stats.most_no_ratings.title') . '</span>',
                'line2' => $this->translator->trans('stats.most_no_ratings.count', array('%user%' => $this->returnUserWithAvatar($results[0], true), '%count%' => '<b><span class="animated-increment" data-value="' . $results['total'] . '">0</span></b>')),
                'line3' => $this->translator->trans('stats.most_no_ratings.total', array('%total%' => '<b><span class="animated-increment" data-value="' . $total['total'] . '">0</span></b>'))
            );
            return $output;
        } else {
            return false;
        }
    }
    
    private function get_most_active_user_last_hour($event) {
        $minCheckins = 2;
        $results = $this->em->getRepository('\App\Entity\User\User')->getMostCheckinsCount(null, $event->getVenues(), new \DateTime('- 1 hour'));
        // Minimum 2 check-ins to be relevant
        if ($results && $results['total'] >= $minCheckins) {
            $total = $this->em->getRepository('\App\Entity\Checkin\Checkin')->getTotalCheckinsCount(null, $event->getVenues(), new \DateTime('- 1 hour'));
            $output = array(
                'line1' => '<span class="info-major">' . $this->translator->trans('stats.most_active.title') . ' (' . $this->translator->trans('stats.most_active.last_hour') . ')</span>',
                'line2' => $this->translator->trans('stats.most_active.count', array('%user%' => $this->returnUserWithAvatar($results[0], true), '%count%' => '<b><span class="animated-increment" data-value="' . $results['total'] . '">0</span></b>')),
                'line3' => $this->translator->trans('stats.most_active.total', array('%total%' => '<b><span class="animated-increment" data-value="' . $total . '">0</span></b>'))
            );
            return $output;
        } else {
            return false;
        }
    }
    
    private function get_most_active_user_four_hours($event) {
        $minCheckins = 5;
        $results = $this->em->getRepository('\App\Entity\User\User')->getMostCheckinsCount(null, $event->getVenues(), new \DateTime('- 4 hour'));
        // Minimum 5 check-ins to be relevant
        if ($results && $results['total'] >= $minCheckins) {
            $total = $this->em->getRepository('\App\Entity\Checkin\Checkin')->getTotalCheckinsCount(null, $event->getVenues(), new \DateTime('- 4 hour'));
            $output = array(
                'line1' => '<span class="info-major">' . $this->translator->trans('stats.most_active.title') . ' (' . $this->translator->trans('stats.most_active.last_4_hours') . ')</span>',
                'line2' => $this->translator->trans('stats.most_active.count', array('%user%' => $this->returnUserWithAvatar($results[0], true), '%count%' => '<b><span class="animated-increment" data-value="' . $results['total'] . '">0</span></b>')),
                'line3' => $this->translator->trans('stats.most_active.total', array('%total%' => '<b><span class="animated-increment" data-value="' . $total . '">0</span></b>'))
            );
            return $output;
        } else {
            return false;
        }
    }
    
    private function get_most_active_user_today($event) {
        $minCheckins = 5;
        $results = $this->em->getRepository('\App\Entity\User\User')->getMostCheckinsCount(null, $event->getVenues(), new \DateTime('today midnight'));
        // Minimum 5 check-ins to be relevant
        if ($results && $results['total'] >= $minCheckins) {
            $total = $this->em->getRepository('\App\Entity\Checkin\Checkin')->getTotalCheckinsCount(null, $event->getVenues(), new \DateTime('today midnight'));
            $output = array(
                'line1' => '<span class="info-major">' . $this->translator->trans('stats.most_active.title') . ' (' . $this->translator->trans('stats.most_active.today') . ')</span>',
                'line2' => $this->translator->trans('stats.most_active.count', array('%user%' => $this->returnUserWithAvatar($results[0], true), '%count%' => '<b><span class="animated-increment" data-value="' . $results['total'] . '">0</span></b>')),
                'line3' => $this->translator->trans('stats.most_active.total', array('%total%' => '<b><span class="animated-increment" data-value="' . $total . '">0</span></b>'))
            );
            return $output;
        } else {
            return false;
        }
    }
    
    
    private function get_most_active_user_whole_event($event) {
        $minCheckins = 5;
        $results = $this->em->getRepository('\App\Entity\User\User')->getMostCheckinsCount(null, $event->getVenues(), $event->getStartDate(), $event->getEndDate());
        // Minimum 5 check-ins to be relevant
        if ($results && $results['total'] >= $minCheckins) {
            $total = $this->em->getRepository('\App\Entity\Checkin\Checkin')->getTotalCheckinsCount(null, $event->getVenues(), $event->getStartDate(), $event->getEndDate());
            $output = array(
                'line1' => '<span class="info-major">' . $this->translator->trans('stats.most_active.title') . '</span>',
                'line2' => $this->translator->trans('stats.most_active.count', array('%user%' => $this->returnUserWithAvatar($results[0], true), '%count%' => '<b><span class="animated-increment" data-value="' . $results['total'] . '">0</span></b>')),
                'line3' => $this->translator->trans('stats.most_active.total', array('%total%' => '<b><span class="animated-increment" data-value="' . $total . '">0</span></b>'))
            );
            return $output;
        } else {
            return false;
        }
    }
    
    private function get_style_with_most_beers($event) {
        if ($results = $this->em->getRepository('\App\Entity\Beer\Style')->getMostCheckedInStyleUniqueBeers(null, $event->getVenues(), $event->getStartDate())) {
            $output = array(
                'line1' => '<span class="info-major">' . $this->translator->trans('stats.popular_style.title') . '</span>',
                'line2' => $results[0]->getName(),
                'line3' => $this->translator->trans('stats.popular_style.total', array('%count%' => '<b><span class="animated-increment" data-value="' . $results['total'] . '">0</span></b>'))
            );
            return $output;
        } else {
            return false;
        }
    }
    
    private function get_unique_beers_count($event) {
        $minBeers = 3;
        $count = $this->em->getRepository('\App\Entity\Beer\Beer')->getUniqueCheckedInBeersCount(null, $event->getVenues(), $event->getStartDate());
        // Minimum 3 beers to be relevant
        if ($count > $minBeers) {
            $latestBeers = $this->em->getRepository('\App\Entity\Beer\Beer')->getUniqueLatestCheckedInBeers(30, null, null, $event->getVenues(), $event->getStartDate(), null, true);
            $output = array(
                'line1' => '<span class="info-major">' . $this->translator->trans('stats.unique_beers.title') . '</span>',
                'line2' => $this->translator->trans('stats.unique_beers.total', array('%count%' => '<b><span class="animated-increment" data-value="' . $count . '">0</span></b>')),
                'line3' => ''
            );
            foreach($latestBeers as $beer) {
                $output['line3'] .= '<div class="image-wrapper"><img src="' . $beer->getLabel() . '"/></div> ';
            }
            return $output;
        } else {
            return false;
        }
    }
    
    private function get_unique_users_count_today($event) {
        $minUsers = 8;
        $count = $this->em->getRepository('\App\Entity\User\User')->getUniqueUsersWithCheckinsCount($event->getVenues(), new \DateTime('today midnight'));
        // Minimum 8 users to be relevant
        if ($count >= $minUsers) {
            $latestUsers = $this->em->getRepository('\App\Entity\User\User')->getUniqueLatestCheckInUsers(30, $event->getVenues(), new \DateTime('today midnight'), null, false);
            $output = array(
                'line1' => '<span class="info-major">' . $this->translator->trans('stats.unique_users.title') . '</span>',
                'line2' => $this->translator->trans('stats.unique_beers.total', array('%count%' => '<b><span class="animated-increment" data-value="' . $count . '">0</span></b>')),
                'line3' => ''
            );
            foreach($latestUsers as $user) {
                $output['line3'] .= '<div class="image-wrapper"><img src="' . $user->getUserAvatar() . '"/></div> ';
            }
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