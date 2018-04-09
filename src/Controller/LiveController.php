<?php

namespace App\Controller;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;

class LiveController extends Controller
{
    /**
     * @Route("/live/venue/{vid}", name="live_venue")
     */
    public function venue($vid)
    {
        $em = $this->getDoctrine()->getManager();
        $venue = $em->getRepository('\App\Entity\Venue\Venue')->find($vid);
        
        if (!$venue) {
            $this->createNotFoundException('This venue is unknown');
        }
        
        $checkins = $em->getRepository('App\Entity\Checkin\Checkin')->getVenueCheckins($vid, null, 100);
        
        return $this->render('live/venue.html.twig', [
            'venue' => $venue,
            'checkins' => $checkins
        ]);
    }
    
    /**
     * @Route("/live/event/{eventID}", name="live_event")
     */
    public function event($eventID)
    {        
        $em = $this->getDoctrine()->getManager();
        $event = $em->getRepository('\App\Entity\Event\Event')->find($eventID);
        
        $session = $this->get('session');
        if ($session->has("_locale") && $event->getLocale() !== $session->get("_locale")) {
            $session->set("_locale", $event->getLocale());
            return new RedirectResponse('/live/event/'.$event->getId());
        }
        
        if (!$event) {
            $this->createNotFoundException('This event is unknown');
        }
        
        $venues = $event->getVenues();
        
        $checkins = $em->getRepository('App\Entity\Checkin\Checkin')->getVenueCheckins($venues, null, 100);
        
        return $this->render('live/event.html.twig', [
            'event' => $event,
            'checkins' => $checkins
        ]);
    }
}
