<?php

namespace App\Controller;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType; 
use App\Entity\Event\Message;

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
        
        if (is_null($event)) {
            $event = $em->getRepository('\App\Entity\Event\Event')->findBySlug($eventID)[0];
            if (is_null($event)) {
                throw $this->createNotFoundException('Unkown event');
            }
        }
        
        $session = $this->get('session');
        if (!$session->has("_locale") || $event->getLocale() !== $session->get("_locale")) {
            $session->set("_locale", $event->getLocale());
            return new RedirectResponse('/live/event/'.$event->getId());
        }
        
        $venues = $event->getVenues();
        
        $checkins = $em->getRepository('App\Entity\Checkin\Checkin')->getVenueCheckins($venues, null, 100, $event->getStartDate());
        
        return $this->render('live/event.html.twig', [
            'event' => $event,
            'checkins' => $checkins
        ]);
    }
    
    
    /**
     * @Route("/post/{eventID}", name="post_message")
     */
    public function post_event_message($eventID, Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $event = $em->getRepository('\App\Entity\Event\Event')->find($eventID);
        
        if (is_null($event)) {
            $event = $em->getRepository('\App\Entity\Event\Event')->findBySlug($eventID)[0];
            if (is_null($event)) {
                throw $this->createNotFoundException('Unkown event');
            }
        }
        
        $message = new Message();
        $message->setEvent($event);
        
        $form = $this->createFormBuilder($message)
        ->add('message_line_1', TextType::class)
        ->add('message_line_1_important', CheckboxType::class,  array('required' => false, 'mapped' => false, 'label' => 'Highlighted'))
        ->add('message_line_2', TextType::class)
        ->add('message_line_2_important', CheckboxType::class,  array('required' => false, 'mapped' => false, 'label' => 'Highlighted'))
        ->add('message_line_3', TextType::class)
        ->add('message_line_3_important', CheckboxType::class,  array('required' => false, 'mapped' => false, 'label' => 'Highlighted'))
        ->add('startTime', TimeType::class,  array('mapped' => false, 'widget' => 'single_text', 'required' => true ))
        ->add('save', SubmitType::class, array('label' => 'Submit Message'))
        ->getForm();
        
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            $message = $form->getData();
            
            // To complicated for no good reason
            $minutesToAdd = ($form->get('startTime')->getData()->format('H') * 60) + $form->get('startTime')->getData()->format('i');
            $startDate = new \DateTime('today midnight');
            $startDate->modify('+'.$minutesToAdd.' minutes');
            $endDate = clone $startDate;
            $endDate = $endDate->modify('+ 10 minutes');
            
            if ($form->get('message_line_1_important')->getData()) {
                $message->setMessageLine1('<span class="info-major">' . $message->getMessageLine1() . '</span>');
            }
            if ($form->get('message_line_2_important')->getData()) {
                $message->setMessageLine2('<span class="info-major">' . $message->getMessageLine2() . '</span>');
            }
            if ($form->get('message_line_3_important')->getData()) {
                $message->setMessageLine3('<span class="info-major">' . $message->getMessageLine3() . '</span>');
            }
            
            $message->setStartDate($startDate);
            $message->setEndDate($endDate);
            $em->persist($message);
            $em->flush();
            return $this->redirectToRoute('post_message_success', array('eventID' => $eventID));
        }
        
        return $this->render('live/post.html.twig', array(
            'form' => $form->createView(),
            'event' => $event,
            'success' => false
        ));
    }
    
    /**
     * @Route("/post/{eventID}/success", name="post_message_success")
     */
    public function post_event_message_success($eventID)
    {
        $em = $this->getDoctrine()->getManager();
        $event = $em->getRepository('\App\Entity\Event\Event')->find($eventID);
        
        if (is_null($event)) {
            $event = $em->getRepository('\App\Entity\Event\Event')->findBySlug($eventID)[0];
            if (is_null($event)) {
                throw $this->createNotFoundException('Unkown event');
            }
        }
        
        return $this->render('live/post.html.twig', array(
            'event' => $event,
            'success' => true
        ));
    }
    
    /**
     * @Route("/notify/{eventID}", name="notification_subscriber")
     */
    public function notification_subscriber($eventID)
    {
        $em = $this->getDoctrine()->getManager();
        $event = $em->getRepository('\App\Entity\Event\Event')->find($eventID);
        
        if (is_null($event)) {
            $event = $em->getRepository('\App\Entity\Event\Event')->findBySlug($eventID)[0];
            if (is_null($event)) {
                throw $this->createNotFoundException('This event is unknown');
            }
        }
        
        $session = $this->get('session');
        if ($session->has("_locale") && $event->getLocale() !== $session->get("_locale")) {
            $session->set("_locale", $event->getLocale());
            return new RedirectResponse('/live/event/'.$event->getId());
        }
        
        return $this->render('live/notification.html.twig', [
            'event' => $event,
        ]);
    }
}
