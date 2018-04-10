<?php

namespace App\Controller;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use JMS\Serializer\SerializerBuilder;
use JMS\Serializer\SerializationContext;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Service\EventStats;

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
     * @Route("/ajax/reloadClients", name="reload_clients")
     */
    public function reloadClientsAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $id = $request->query->get('id');
        $event = $em->getRepository('App\Entity\Event\Event')->find($id);
        
        $magicArray = array(
            'push_topic' => 'info-event-'.$event->getId(),
            'push_type' => 'info',
            'action' => 'reload'
        );
        
        $context = new \ZMQContext();
        $socket = $context->getSocket(\ZMQ::SOCKET_PUSH, 'onNewMessage');
        $socket->connect("tcp://localhost:5555");
        $socket->send(json_encode($magicArray));
        
        // redirect to the 'list' view of the given entity
        return $this->redirectToRoute('easyadmin', array(
            'action' => 'list',
            'entity' => $request->query->get('entity'),
        ));
    }
}
