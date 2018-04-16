<?php

namespace App\Controller;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use App\Service\EventStats;

class EventController extends Controller
{
    /**
     * @Route("/event/{eventID}", name="event")
     */
    public function index($eventID, EventStats $stats)
    {
        $em = $this->getDoctrine()->getManager();
        $event = $em->getRepository('\App\Entity\Event\Event')->find($eventID);
        
        if (is_null($event)) {
            $event = $em->getRepository('\App\Entity\Event\Event')->findBySlug($eventID)[0];
            if (is_null($event)) {
                throw $this->createNotFoundException('Unknown event');
            }
        }
        
        $stats = $stats->getStatsCards($event);
        
        $em->getRepository('\App\Entity\Beer\Beer')->getMostCheckedInBeer();
        return $this->render('event/index.html.twig', [
            'event' => $event,
            'stats' => $stats
        ]);
    }
}
