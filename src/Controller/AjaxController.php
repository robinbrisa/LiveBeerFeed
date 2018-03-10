<?php

namespace App\Controller;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use JMS\Serializer\SerializerBuilder;
use JMS\Serializer\SerializationContext;
use Symfony\Component\HttpFoundation\Response;

class AjaxController extends Controller
{
    /**
     * @Route("/ajax/getVenueCheckins/{vid}", name="ajax_get_venue_checkins_since")
     */
    public function getVenueCheckins($vid)
    {
        $minID = null;
        if (isset($_GET['minID'])) {
            $minID = $_GET['minID'];
        }
        
        $em = $this->getDoctrine()->getManager();
        $checkins = $em->getRepository('App\Entity\Checkin\Checkin')->getVenueCheckins($vid, $minID);
                
        $serializer = SerializerBuilder::create()->build();
        $jsonContent = $serializer->serialize($checkins, 'json', SerializationContext::create()->enableMaxDepthChecks());
        
        $response = new Response($jsonContent);
        $response->headers->set('Content-Type', 'application/json');
        
        return $response;
    }
}
