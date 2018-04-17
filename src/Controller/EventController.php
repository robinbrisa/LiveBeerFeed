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
            $event = $em->getRepository('\App\Entity\Event\Event')->findBySlug($eventID);
            if (!$event) {
                throw $this->createNotFoundException('Unknown event');
            }
            $event = $event[0];
        }
        
        $messages = $em->getRepository('\App\Entity\Event\Message')->findLatestEventMessageByBroadcastDate($event, new \DateTime('- 6 hours'));
        $stats = $stats->getStatsCards($event);
        
        return $this->render('event/index.html.twig', [
            'event' => $event,
            'stats' => $stats,
            'messages' => $messages
        ]);
    }
}
