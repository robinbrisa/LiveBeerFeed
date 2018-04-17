<?php

namespace App\Controller;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use App\Service\EventStats;
use App\Service\Tools;

class MainController extends Controller
{
    /**
     * @Route("/", name="homepage")
     */
    public function index()
    {
        $em = $this->getDoctrine()->getManager();
        $events = $em->getRepository('\App\Entity\Event\Event')->findCurrentEvents();
        $upcomingEvents = $em->getRepository('\App\Entity\Event\Event')->findUpcomingEvents();
        $previousEvents = $em->getRepository('\App\Entity\Event\Event')->findPreviousEvents();
        return $this->render('main/index.html.twig', [
            'currentEvents' => $events,
            'upcomingEvents' => $upcomingEvents,
            'previousEvents' => $previousEvents,
        ]);
    }
    
    /**
     * @Route("/debug/{id}", name="debug")
     */
    public function debug($id, EventStats $stats, Tools $tools)
    {
        $em = $this->getDoctrine()->getManager();
        $event = $em->getRepository('\App\Entity\Event\Event')->find($id);
        $venues = $event->getVenues();
        
        $msg = $em->getRepository('\App\Entity\Event\Message')->findLatestEventMessageByBroadcastDate($event, new \DateTime('- 5 hours'));
        dump($msg);
        
        if (count($em->getRepository('App\Entity\Checkin\Checkin')->getVenueCheckins($venues, null, 1)) > 0) {
            echo $em->getRepository('App\Entity\Checkin\Checkin')->getVenueCheckins($venues, null, 1)[0]->getId();
        } else {
            echo "No checkin";
        }
        
        $styles = $em->getRepository('\App\Entity\Beer\Style')->findAll();
        
        foreach ($styles as $style) {
            echo '<div id="info-content" style="width:500px;">';
            echo '<div class="line">';
            echo '<div class="color-wrapper live-style-color-container"><div class="ranking-style-color" style="float:left; background-color: ' . $style->getColor() . '; width:32px;"></div></div>';
            echo '<span class="name">' . $style->getName() . '</span>';
            echo '</div>';
            echo '</div>';
        }
        
        $statsDebug = $stats->debugStatistics($event);
        
        echo '<div style="width: 500px; margin-left: 30px;">';
        foreach ($statsDebug as $function => $value) {
            echo '<div>'.$function.'</div>';
            if ($value) {
                echo '<div id="info-content">';
                echo '<div class="line"><span class="info-line-text">' . $value['line1'] . '</span></div>';
                echo '<div class="line"><span class="info-line-text">' . $value['line2'] . '</span></div>';
                echo '<div class="line"><span class="info-line-text">' . $value['line3'] . '</span></div>';
                echo '</div>';
            } else {
                echo '<div>Returned false</div>';
            }
        }
        echo '</div>';
        
        return $this->render('main/debug.html.twig', [
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
