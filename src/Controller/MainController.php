<?php

namespace App\Controller;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class MainController extends Controller
{
    /**
     * @Route("/", name="homepage")
     */
    public function index()
    {
        $em = $this->getDoctrine()->getManager();
        $events = $em->getRepository('\App\Entity\Event\Event')->findCurrentEvents();
        $venues = $events[0]->getVenues();
        dump($em->getRepository('\App\Entity\Checkin\Checkin')->getCheckinWithMostBadges(2278575, null));
        
        return $this->render('main/index.html.twig', [
            'currentEvents' => $events,
        ]);
    }
    
    /**
     * @Route("/global", name="global_stats")
     */
    public function globalStats()
    {
        $em = $this->getDoctrine()->getManager();
        $stats = array();
        
        $stats['total_checkins'] = $em->getRepository('\App\Entity\Checkin\Checkin')->getTotalCheckinsCount();
        $stats['most_toasts'] = $em->getRepository('\App\Entity\Checkin\Checkin')->getCheckinWithMostToasts();
        $stats['most_comments'] = $em->getRepository('\App\Entity\Checkin\Checkin')->getCheckinWithMostComments();
        $stats['most_badges'] = $em->getRepository('\App\Entity\Checkin\Checkin')->getCheckinWithMostBadges();
        $stats['rating_avg'] = $em->getRepository('\App\Entity\Checkin\Checkin')->getAverageRatingByCheckin();
        $stats['ratings_by_score'] = $em->getRepository('\App\Entity\Checkin\Checkin')->getRatingsCountByScore();
        $stats['most_checked_in_beer'] = $em->getRepository('\App\Entity\Beer\Beer')->getMostCheckedInBeer();
        $stats['most_checked_in_brewery'] = $em->getRepository('\App\Entity\Brewery\Brewery')->getMostCheckedInBrewery();
        $stats['most_checked_in_brewery_unique'] = $em->getRepository('\App\Entity\Checkin\Checkin')->getMostCheckedInUniqueBrewery();
        $stats['best_rated_brewery'] = $em->getRepository('\App\Entity\Checkin\Checkin')->getBestRatedBrewery();
        $stats['checkin_history_per_day'] = $em->getRepository('\App\Entity\Checkin\Checkin')->getCheckinHistoryPerDay();
        $stats['day_with_most_checkins'] = $em->getRepository('\App\Entity\Checkin\Checkin')->getDayWithMostCheckins();
        $stats['month_with_most_checkins'] = $em->getRepository('\App\Entity\Checkin\Checkin')->getMonthWithMostCheckins();
        $stats['year_with_most_checkins'] = $em->getRepository('\App\Entity\Checkin\Checkin')->getYearWithMostCheckins();
        
        $ratings = array('0.25', '0.50', '0.75', '1.00', '1.25', '1.50', '1.75', '2.00', '2.25', '2.50', '2.75', '3.00', '3.25', '3.50', '3.75', '4.00', '4.25', '4.50', '4.75', '5.00');
        
        return $this->render('main/global.html.twig', [
            'stats' => $stats,
            'ratings' => $ratings
        ]);
    }
}
