<?php

namespace App\Controller;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class VenueController extends Controller
{
    /**
     * @Route("/venue/{vid}", name="venue_info")
     */
    public function viewInfo($vid)
    {
        $em = $this->getDoctrine()->getManager();
        $venue = $em->getRepository('\App\Entity\Venue\Venue')->find($vid);
        
        if (!$venue) {
            $this->createNotFoundException('This venue is unknown');
        }
        
        return $this->render('venue/index.html.twig', [
            'venue' => $venue,
        ]);
    }
}
