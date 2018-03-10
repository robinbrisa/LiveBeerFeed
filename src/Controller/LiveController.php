<?php

namespace App\Controller;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class LiveController extends Controller
{
    /**
     * @Route("/live/venue/{vid}", name="live_venue")
     */
    public function index($vid)
    {
        $em = $this->getDoctrine()->getManager();
        $venue = $em->getRepository('\App\Entity\Venue\Venue')->find($vid);
        
        if (!$venue) {
            $this->createNotFoundException('This venue is unknown');
        }
        
        $checkins = $em->getRepository('App\Entity\Checkin\Checkin')->getVenueCheckins($vid, 20);
        
        return $this->render('live/venue.html.twig', [
            'venue' => $venue,
            'checkins' => $checkins
        ]);
    }
}
