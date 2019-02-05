<?php

namespace App\Controller;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use JMS\Serializer\SerializerBuilder;
use JMS\Serializer\SerializationContext;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Service\EventStats;
use App\Entity\PushSubscription;
use App\Entity\User\SavedData;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use App\Service\UntappdAPI;

class AjaxController extends Controller
{
    /**
     * @Route("/ajax/getLiveCheckins/{type}/{id}", name="ajax_get_live_checkins_since")
     */
    public function getLiveCheckins($type, $id, $format = 'json')
    {
        $minID = null;
        if (isset($_GET['minID'])) {
            $minID = $_GET['minID'];
        }
        $format = 'json';
        if (isset($_GET['format'])) {
            $format = $_GET['format'];
        }
        
        $em = $this->getDoctrine()->getManager();
        if ($type == "venue") {
            $checkins = $em->getRepository('App\Entity\Checkin\Checkin')->getVenueCheckins($id, $minID);
        } elseif ($type == "event") {
            $event = $em->getRepository('\App\Entity\Event\Event')->find($id);
            $checkins = $em->getRepository('App\Entity\Checkin\Checkin')->getVenueCheckins($event->getVenues(), $minID);
        } else {
            throw New \Exception("INVALID LIVE TYPE");
        }
        
        if ($format == 'html') {
            $checkinsArray = array('checkins' => array(), 'medias' => array());
            $mediaCount = 0;
            foreach ($checkins as $checkin) {
                if ($checkin->getMedias()[0]) {
                    $mediaCount++;
                    $checkinsArray['medias'][] = $this->render('live/content/media.template.html.twig', ['checkin' => $checkin, 'i' => $mediaCount])->getContent();
                }
                $checkinsArray['checkins'][] = $this->render('live/content/checkin.template.html.twig', ['checkin' => $checkin, 'i' => $mediaCount])->getContent();
            }
            $checkinsArray['mediaCount'] = count($checkinsArray['medias']);
            $checkinsArray['count'] = count($checkinsArray['checkins']);
            $response = new Response(json_encode($checkinsArray));
        } else {
            $serializer = SerializerBuilder::create()->build();
            $jsonContent = $serializer->serialize($checkins, 'json', SerializationContext::create()->enableMaxDepthChecks());
            $response = new Response($jsonContent);
        }
        
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }
    
    
    /**
     * @Route("/ajax/getEventInfoMessage/{id}", name="ajax_get_event_info_message")
     */
    public function getEventInfoMessage($id, EventStats $stats)
    {
        $em = $this->getDoctrine()->getManager();
        if (!$event = $em->getRepository('\App\Entity\Event\Event')->find($id)) {
            throw New \Exception("UNKNOWN EVENT");
        }
        
        $update = true;
        if((new \DateTime())->getTimestamp() - $event->getLastInfoPolling()->getTimestamp() < 5) {
            $update = false;
        }
        
        
        $output = array();
        $output['line1'] = '<span class="info-major">' . $this->get('translator')->trans('live.welcome') . '</span>';
        $output['line2'] = '<span class="info-major">' . $event->getName() . '</span>';
        $output['line3'] = $event->getStartDate()->format('d/m/Y') . ' - ' . $event->getEndDate()->format('d/m/Y');
        if ($event->getLastInfoStats()) {
            if ($update) {
                $event->setLastInfoStats(0);
            }
            if ($message = $em->getRepository('\App\Entity\Event\Message')->findInfoMessageToDisplay($event)) {
                $output['line1'] = $message->getMessageLine1();
                $output['line2'] = $message->getMessageLine2();
                $output['line3'] = $message->getMessageLine3();
                if ($update) {
                    $event->setLastInfoPolling(new \DateTime());
                    $message->setLastTimeDisplayed(new \DateTime());
                }
            }
        } else {
            if ($statistics = $stats->returnRandomStatistic($event)) {
                $output = $statistics;
            }
            if ($update) {
                $event->setLastInfoStats(1);
                $event->setLastInfoPolling(new \DateTime());
            }
        }
        
        $em->persist($event);
        $em->flush();
        
        $response = new Response(json_encode($output));
        $response->headers->set('Content-Type', 'application/json');
        
        return $response;
    }
    
    /**
     * @Route("/ajax/pushSubscription", name="ajax_push_subscription")
     */
    public function pushSubscriptionAction()
    {
        $em = $this->getDoctrine()->getManager();
        $request = Request::createFromGlobals();
        $method = $request->server->get('REQUEST_METHOD');
        $event = $em->getRepository('\App\Entity\Event\Event')->find($request->request->get('event'));
        if (!$event) {
            throw New \Exception("Invalid event");
        }
        switch ($method) {
            case 'POST':
                $pushSubscription = new PushSubscription();
                $pushSubscription->setEndpoint($request->request->get('endpoint'));
                $pushSubscription->setPublicKey($request->request->get('publicKey'));
                $pushSubscription->setAuthToken($request->request->get('authToken'));
                $pushSubscription->setContentEncoding($request->request->get('contentEncoding'));
                $pushSubscription->setEvent($event);
                $em->persist($pushSubscription);
                break;
            case 'PATCH':
                $pushSubscription = $em->getRepository('\App\Entity\PushSubscription')->findOneBy(array('endpoint' => $request->request->get('endpoint'), 'event' => $event));
                if (!$pushSubscription) {
                    $pushSubscription = new PushSubscription();
                    $pushSubscription->setEndpoint($request->request->get('endpoint'));
                    $pushSubscription->setEvent($event);
                }
                $pushSubscription->setPublicKey($request->request->get('publicKey'));
                $pushSubscription->setAuthToken($request->request->get('authToken'));
                $pushSubscription->setContentEncoding($request->request->get('contentEncoding'));
                $em->persist($pushSubscription);
                break;
            case 'DELETE':
                $pushSubscription = $em->getRepository('\App\Entity\PushSubscription')->findOneBy(array('endpoint' => $request->request->get('endpoint'), 'event' => $event));
                if ($pushSubscription) {
                    $em->remove($pushSubscription);
                }
                break;
            default:
                throw New \Exception("Invalid action");
                break;
        }
        $em->flush();
        $output = array('success' => true);
        $response = new Response(json_encode($output));
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }
    
    /**
     * @Route("/ajax/saveTaplistData", name="ajax_save_taplist_data")
     */
    public function saveTaplistDataAction(Request $request)
    {
        $output = array('success' => true);
        $em = $this->getDoctrine()->getManager();
        $ticks = $request->request->get('ticks');
        $favorites = $request->request->get('favorites');
        $buttonAction = $request->request->get('buttonAction');
        $event = $request->request->get('event');
        $tickCheckIn = $request->request->get('tickCheckIn');
        if ($tickCheckIn == "false") {
            $tickCheckIn = false;
        }
        $session = $request->getSession();
        if (!$userUntappdID = $session->get('userUntappdID')) {
            $output['success'] = false;
            $output['error'] = 'INVALID_USER';
        } else {
            $user = $em->getRepository('\App\Entity\User\User')->find($userUntappdID);
        }
        if (!$event = $em->getRepository('\App\Entity\Event\Event')->find($event)) {
            $output['success'] = false;
            $output['error'] = 'INVALID_EVENT';
        }
        if ($output['success']) {
            if (!$userData = $em->getRepository('\App\Entity\User\SavedData')->findOneBy(array('user' => $user, 'event' => $event))) {
                $userData = new SavedData();
                $userData->setUser($user);
                $userData->setEvent($event);
            }
            $userData->setButtonAction($buttonAction);
            $userData->setTicks($ticks);
            $userData->setFavorites($favorites);
            $userData->setTickedCheckedIn($tickCheckIn);
        }
        $em->persist($userData);
        $em->flush();
        
        $response = new Response(json_encode($output));
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }
    
    /**
     * @Route("/ajax/quickCheckInModal/{event_id}/{beer_id}", name="ajax_quick_checkin_modal")
     */
    public function quickCheckInModalAction($event_id, $beer_id, Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        if (!$event = $em->getRepository('\App\Entity\Event\Event')->find($event_id)) {
            throw new NotFoundHttpException("Invalid Event ID");
        } else {
            $venue = $event->getVenues()[0];
        }
        if (!$beer = $em->getRepository('\App\Entity\Beer\Beer')->find($beer_id)) {
            throw new NotFoundHttpException("Invalid Beer ID");
        }        
        
        return $this->render('taplist/templates/quick-checkin.template.html.twig', [
            'beer' => $beer,
            'venue' => $venue
        ]);
    }
    
    /**
     * @Route("/ajax/addCheckin", name="ajax_quick_checkin_post")
     */
    public function addCheckinAction(Request $request, UntappdAPI $untappdAPI)
    {
        $session = $request->getSession();
        $output = array('success' => true);
        if (!$userUntappdID = $session->get('userUntappdID')) {
            $output['success'] = false;
            $output['error'] = 'NOT_LOGGED_IN';
        } else {
            $em = $this->getDoctrine()->getManager();
            $user = $em->getRepository('\App\Entity\User\User')->find($userUntappdID);
            if (!$user->getInternalUntappdAccessToken()) {
                $output['success'] = false;
                $output['error'] = 'MISSING_AUTH_TOKEN';
            }
            if (!$beer = $em->getRepository('\App\Entity\Beer\Beer')->find($request->request->get('beerId'))) {
                $output['success'] = false;
                $output['error'] = 'INVALID_BEER';
            }
            if (!$venue = $em->getRepository('\App\Entity\Venue\Venue')->find($request->request->get('venueId'))) {
                $output['success'] = false;
                $output['error'] = 'INVALID_VENUE';
            }
            $comment = null;
            if ($request->request->get('checkinComment') != "") {
                $comment = $request->request->get('checkinComment');
            }
            $rating = null;
            if ($request->request->get('ratingScoreRange') > 0) {
                $rating = $request->request->get('ratingScoreRange');
            }
            
            $checkin = $untappdAPI->addCheckin($user->getInternalUntappdAccessToken(), $beer->getId(), $comment, $rating, $venue->getFoursquareId(), $venue->getLatitude(), $venue->getLongitude());
            
            if (!isset($checkin->body->response->result) || $checkin->body->response->result != "success") {
                $output['success'] = false;
                $output['error'] = 'CHECKIN_ERROR';
            } else {
                $output['response'] = $checkin->body->response;
                $output['display'] = $this->render('taplist/templates/quick-checkin-success.template.html.twig', [
                    'response' => $checkin->body->response,
                    'beer' => $beer
                ])->getContent();
            }
        
        }
        
        $response = new Response(json_encode($output));
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }
    
    /**
     * @Route("/ajax/setOutOfStock", name="ajax_set_out_of_stock")
     */
    public function setOutOfStockAction(Request $request, UntappdAPI $untappdAPI)
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        $output = array('success' => true);
        $em = $this->getDoctrine()->getManager();
        
        $sessionID = $request->request->get('sessionID');
        $beerID = $request->request->get('beerID');
        $action = $request->request->get('action');
        
        if (!$session = $em->getRepository('\App\Entity\Event\Session')->find($sessionID)) {
            $output['success'] = false;
            $output['error'] = 'INVALID_SESSION';
        }
        
        if (!$beer = $em->getRepository('\App\Entity\Beer\Beer')->find($beerID)) {
            $output['success'] = false;
            $output['error'] = 'INVALID_BEER';
        }
        
        if ($action != "ADD" && $action != "REMOVE") {
            $output['success'] = false;
            $output['error'] = 'INVALID_ACTION';
        }
        
        if ($output['success']) {
            if ($action == "ADD" && !$session->getOutOfStock()->contains($beer)) {
                $session->addOutOfStock($beer);
            } elseif ($action == "REMOVE") {
                $session->removeOutOfStock($beer);
            }
            $em->persist($session);
            $em->flush();
            $outOfStockFull = array();
            $output['outOfStock'] = $session->getOutOfStock();
            foreach($session->getEvent()->getSessions() as $sess) {
                if (!array_key_exists($sess->getId(), $outOfStockFull)) {
                    $outOfStockFull[$sess->getId()] = array();
                }
                foreach($sess->getOutOfStock() as $beer) {
                    $outOfStockFull[$sess->getId()][] = $beer->getId();
                }
            }
            $pushData = array(
                'push_type' => 'out_of_stock',
                'push_topic' => 'taplist-'.$session->getEvent()->getId().'-all',
                'list' => $outOfStockFull,
                'session' => $session->getId()
            );
            $context = new \ZMQContext();
            $socket = $context->getSocket(\ZMQ::SOCKET_PUSH, 'onNewMessage');
            $socket->connect("tcp://localhost:5555");
            $socket->send(json_encode($pushData));
            $socket->disconnect("tcp://localhost:5555");
        }
        
        $response = new Response(json_encode($output));
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }
    
    /**
     * @Route("/ajax/selectSearchResult", name="ajax_select_search_result")
     */
    public function selectSearchResultAction(Request $request)
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        $output = array('success' => true);
        $em = $this->getDoctrine()->getManager();
        
        $resultID = $request->request->get('resultID');
        
        if (!$result = $em->getRepository('\App\Entity\Search\Result')->find($resultID)) {
            $output['success'] = false;
            $output['error'] = 'INVALID_RESULT';
        } else {
            foreach ($result->getElement()->getResults() as $aResult) {
                $aResult->setSelected(0);
                $em->persist($aResult);
            }
            $result->setSelected(1);
            $em->persist($result);
            $em->flush();
        }
        
        $response = new Response(json_encode($output));
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }
    
    /**
     * @Route("/ajax/taplistAdminModal/{session_id}/{beer_id}", name="ajax_taplist_admin_modal")
     */
    public function taplistAdminModal($session_id, $beer_id, Request $request)
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        $em = $this->getDoctrine()->getManager();
        if (!$session = $em->getRepository('\App\Entity\Event\Session')->find($session_id)) {
            throw new NotFoundHttpException("Invalid Session ID");
        }
        if (!$beer = $em->getRepository('\App\Entity\Beer\Beer')->find($beer_id)) {
            throw new NotFoundHttpException("Invalid Beer ID");
        }
        
        return $this->render('taplist/templates/beer-admin.html.twig', [
            'session' => $session,
            'beer' => $beer
        ]);
    }
    
    /**
     * @Route("/ajax/removeFromTaplist", name="ajax_remove_from_taplist")
     */
    public function removeFromTaplist(Request $request)
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        $em = $this->getDoctrine()->getManager();
        $output = array('success' => true);
        $sessionID = $request->request->get('sessionID');
        $beerID = $request->request->get('beerID');
        $em = $this->getDoctrine()->getManager();
        if (!$session = $em->getRepository('\App\Entity\Event\Session')->find($sessionID)) {
            $output['success'] = false;
            $output['error'] = 'INVALID_SESSION';
        }
        if (!$beer = $em->getRepository('\App\Entity\Beer\Beer')->find($beerID)) {
            $output['success'] = false;
            $output['error'] = 'INVALID_BEER';
        }
        
        $session->removeBeer($beer);
        $em->persist($session);
        $em->flush();
        
        $pushData = array(
            'push_type' => 'remove',
            'push_topic' => 'taplist-'.$session->getEvent()->getId().'-all',
            'session' => $session->getId(),
            'beer' => $beer->getId()
        );
        $context = new \ZMQContext();
        $socket = $context->getSocket(\ZMQ::SOCKET_PUSH, 'onNewMessage');
        $socket->connect("tcp://localhost:5555");
        $socket->send(json_encode($pushData));
        $socket->disconnect("tcp://localhost:5555");
        
        $response = new Response(json_encode($output));
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }
    
    /**
     * @Route("/ajax/searchBeer", name="ajax_search_beer")
     */
    public function searchBeer(Request $request)
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        $output = array('success' => true);
        $searchString = $request->request->get('searchString');
        
        $apiKeyPool = $this->tools->getAPIKeysPool();
        if ((array_sum($apiKeyPool) < 40 && $this->em->getRepository('\App\Entity\Event\Event')->findCurrentEvents()) || array_sum($apiKeyPool) < 5) {
            $output['success'] = false;
            $output['error'] = 'NOT_ENOUGH_API_KEYS';
        } else {
            $apiKey = $this->tools->getBestAPIKey($apiKeyPool);
            if ($response = $this->untappdAPI->searchBeer($searchString, $apiKey)) {
                $output['count'] = $response->body->response->beers->count;
                $found = $this->untappdAPISerializer->handleSearchResults($response->body->response->beers->items);
                foreach ($found as $beer) {
                    $output['results'][$beer->getId()] = $beer->getName() . ' (' . $beer->getBrewery()->getName() . ')';
                }
            } else {
                $output['success'] = false;
                $output['error'] = 'SEARCH_FAILED';
            }
        }
        
        $response = new Response(json_encode($output));
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }
    
}
