<?php

namespace App\Controller;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class UserController extends Controller
{
    /**
     * @Route("/user/{username}", name="user_profile")
     */
    public function viewProfile($username)
    {
        $em = $this->getDoctrine()->getManager();
        $stats = array();
        $user = $em->getRepository('\App\Entity\User\User')->findOneBy(array('user_name' => $username));
        $stats['toasts_received'] = $em->getRepository('\App\Entity\Checkin\Toast')->getTotalToastsToUser($user);
        $stats['toasts_done'] = $em->getRepository('\App\Entity\Checkin\Toast')->getTotalToastsByUser($user);
        $stats['comments_received'] = $em->getRepository('\App\Entity\Checkin\Comment')->getTotalCommentsToUser($user);
        $stats['comments_done'] = $em->getRepository('\App\Entity\Checkin\Comment')->getTotalCommentsByUser($user);
        $stats['most_toasts'] = $em->getRepository('\App\Entity\Checkin\Checkin')->getCheckinWithMostToasts($user);
        $stats['most_comments'] = $em->getRepository('\App\Entity\Checkin\Checkin')->getCheckinWithMostComments($user);
        $stats['most_badges'] = $em->getRepository('\App\Entity\Checkin\Checkin')->getCheckinWithMostBadges($user); 
        $stats['rating_avg'] = $em->getRepository('\App\Entity\Checkin\Checkin')->getAverageRatingByCheckin($user);
        $stats['ratings_by_score'] = $em->getRepository('\App\Entity\Checkin\Checkin')->getRatingsCountByScore($user);
        $stats['most_checked_in_beer'] = $em->getRepository('\App\Entity\Checkin\Checkin')->getMostCheckedInBeer($user);
        $stats['most_checked_in_brewery'] = $em->getRepository('\App\Entity\Checkin\Checkin')->getMostCheckedInBrewery($user);
        $stats['most_checked_in_brewery_unique'] = $em->getRepository('\App\Entity\Checkin\Checkin')->getMostCheckedInUniqueBrewery($user);
        $stats['best_rated_brewery'] = $em->getRepository('\App\Entity\Checkin\Checkin')->getBestRatedBrewery($user);
        $stats['checkin_history_per_day'] = $em->getRepository('\App\Entity\Checkin\Checkin')->getCheckinHistoryPerDay($user);
        $stats['day_with_most_checkins'] = $em->getRepository('\App\Entity\Checkin\Checkin')->getDayWithMostCheckins($user);
        $stats['month_with_most_checkins'] = $em->getRepository('\App\Entity\Checkin\Checkin')->getMonthWithMostCheckins($user);
        $stats['year_with_most_checkins'] = $em->getRepository('\App\Entity\Checkin\Checkin')->getYearWithMostCheckins($user);
        $stats['most_visited_venue'] = $em->getRepository('\App\Entity\Venue\Venue')->getMostVisitedVenue($user);
        
        $ratings = array('0.25', '0.50', '0.75', '1.00', '1.25', '1.50', '1.75', '2.00', '2.25', '2.50', '2.75', '3.00', '3.25', '3.50', '3.75', '4.00', '4.25', '4.50', '4.75', '5.00');
        
        return $this->render('user/index.html.twig', [
            'user' => $user,
            'stats' => $stats,
            'ratings' => $ratings
        ]);
    }
}
