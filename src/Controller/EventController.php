<?php

namespace App\Controller;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class EventController extends Controller
{
    /**
     * @Route("/event/{eventID}", name="event")
     */
    public function index($eventID)
    {
        $minBeerRatings = 5;
        $minBreweryRatings = 5;
        
        $em = $this->getDoctrine()->getManager();
        $event = $em->getRepository('\App\Entity\Event\Event')->find($eventID);
        
        if (is_null($event)) {
            $event = $em->getRepository('\App\Entity\Event\Event')->findBySlug($eventID)[0];
            if (is_null($event)) {
                throw $this->createNotFoundException('Unkown event');
            }
        }
        
        $venues = $event->getVenues();
        
        $stats = array();
        
        if ($bestRatedBeers = $em->getRepository('\App\Entity\Beer\Beer')->getBestRatedBeer(null, $venues, $event->getStartDate(), $event->getEndDate(), $minBeerRatings, 5)) {
            $stats['best_rated_beers'] = array(
                'label' => 'Best Rated Beers',
                'template' => 'best_rated_beers',
                'content' => $bestRatedBeers
            );
        }
        if ($bestRatedBreweries = $em->getRepository('\App\Entity\Brewery\Brewery')->getBestRatedBrewery(null, $venues, $event->getStartDate(), $event->getEndDate(), $minBreweryRatings, 5)) {
            $stats['best_rated_breweries'] = array(
                'label' => 'Best Rated Breweries',
                'template' => 'best_rated_breweries',
                'content' => $bestRatedBreweries
            );
        }
        if ($mostRatedBeers = $em->getRepository('\App\Entity\Beer\Beer')->getMostCheckedInBeer(null, $venues, $event->getStartDate(), $event->getEndDate(), 5)) {
            $stats['most_rated_beers'] = array(
                'label' => 'Most Checked-in Beers',
                'template' => 'most_rated_beers',
                'content' => $mostRatedBeers
            );
        }
        
        if ($mostRatedBreweries = $em->getRepository('\App\Entity\Brewery\Brewery')->getMostCheckedInBrewery(null, $venues, $event->getStartDate(), $event->getEndDate(), 5)) {
            $stats['most_rated_breweries'] = array(
                'label' => 'Most Checked-in Breweries',
                'template' => 'most_rated_breweries',
                'content' => $mostRatedBreweries
            );
        }
        
        
        $em->getRepository('\App\Entity\Beer\Beer')->getMostCheckedInBeer();
        return $this->render('event/index.html.twig', [
            'event' => $event,
            'stats' => $stats
        ]);
    }
}
