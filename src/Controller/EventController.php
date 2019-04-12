<?php

namespace App\Controller;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use App\Service\EventStats;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use App\Entity\Event\Message;
use App\Entity\Event\Publisher;
use App\Entity\Beer\LocalBeer;
use App\Form\Beer\LocalBeerType;
use App\Repository\Beer\LocalBeerRepository;


class EventController extends Controller
{
    /**
     * @Route("/event/{eventID}/", name="event")
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
    
    /**
     * @Route("/e/{eventID}/", name="event_alias")
     */
    public function event_alias($eventID)
    {
        return $this->redirectToRoute('event', array('eventID' => $eventID));
    }
    
    
    /**
     * @Route("/",name="subdomain_alias", host="{subdomain}.livebeerfeed.com", requirements={"subdomain"="lbf3"})
     */
    public function subdomainAlias($subdomain)
    {
        return $this->redirect('https://www.livebeerfeed.com/event/'.$subdomain);
    }
    
    /**
     * @Route("/event/{eventID}/brewery/", name="brewery_portal")
     */
    public function brewery_portal($eventID, Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $event = $em->getRepository('\App\Entity\Event\Event')->find($eventID);
        
        $success = false;
        
        if (is_null($event)) {
            $event = $em->getRepository('\App\Entity\Event\Event')->findBySlug($eventID)[0];
            if (is_null($event)) {
                throw $this->createNotFoundException('Unknown event');
            }
        }
        
        $session = $request->getSession();
        
        if (!$session->get('post_access_key/'.$eventID)) {
            $error = false;
            $form = $this->createFormBuilder()
            ->add('access_key', TextType::class, array('required' => true, 'label' => 'event.form.access_key'))
            ->add('send', SubmitType::class, array('label' => 'event.form.send'))
            ->getForm();
            
            $form->handleRequest($request);
            
            if ($form->isSubmitted() && $form->isValid()) {
                $data = $form->getData();
                $publisher = $em->getRepository('\App\Entity\Event\Publisher')->findOneBy(array('access_key' => $data['access_key']));
                if ($publisher && $publisher->getEvent() === $event) {
                    $session->set('post_access_key/'.$eventID, $data['access_key']);
                    return $this->redirectToRoute('brewery_portal', array('eventID' => ($event->getSlug()?$event->getSlug():$event->getId())));
                } else {
                    $error = true;
                }
            }
            
            return $this->render('event/auth.html.twig', array(
                'form' => $form->createView(),
                'event' => $event,
                'error' => $error,
                'closed' => false
            ));
        } else {
            $authKey = $session->get('post_access_key/'.$eventID);
            $publisher = $em->getRepository('\App\Entity\Event\Publisher')->findOneBy(array('access_key' => $authKey, 'event' => $event));
            if (!$publisher) {
                return $this->redirectToRoute('post_logout', array('eventID' => ($event->getSlug()?$event->getSlug():$event->getId())));
            }
            return $this->render('event/brewery_portal.html.twig', array(
                'event' => $event,
                'publisher' => $publisher,
                'success' => $success
            ));
        }
        
    }
    
    /**
     * @Route("/event/{eventID}/brewery/post/", name="post_message")
     */
    public function post_event_message($eventID, Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $event = $em->getRepository('\App\Entity\Event\Event')->find($eventID);
        
        $success = false;
        
        if (is_null($event)) {
            $event = $em->getRepository('\App\Entity\Event\Event')->findBySlug($eventID)[0];
            if (is_null($event)) {
                throw $this->createNotFoundException('Unknown event');
            }
        }
        
        $session = $request->getSession();
        
        if ($event->getStartDate() > new \DateTime('now') || $event->getEndDate() < new \DateTime('now')) {
            return $this->render('event/post.html.twig', array(
                'form' => null,
                'event' => $event,
                'publisher' => null,
                'success' => $success,
                'closed' => true
            ));
        }
        
        if (!$session->get('post_access_key/'.$eventID)) {
            return $this->redirectToRoute('brewery_portal', array('eventID' => ($event->getSlug()?$event->getSlug():$event->getId())));
        } else {
            $authKey = $session->get('post_access_key/'.$eventID);
            $publisher = $em->getRepository('\App\Entity\Event\Publisher')->findOneBy(array('access_key' => $authKey, 'event' => $event));
            if (!$publisher) {
                return $this->redirectToRoute('post_logout', array('eventID' => ($event->getSlug()?$event->getSlug():$event->getId())));
            }
            
            $message = new Message();
            $message->setEvent($event);
            
            $form = $this->createFormBuilder($message)
            ->add('message_line_1', TextType::class, array('attr' => array('maxlength ' => '65'), 'label' => 'event.form.message_line_1'))
            ->add('message_line_1_important', CheckboxType::class,  array('required' => false, 'mapped' => false, 'label' => 'event.form.highlight'))
            ->add('message_line_2', TextType::class, array('attr' => array('maxlength ' => '65', 'autocapitalize' => 'off'), 'label' => 'event.form.message_line_2'))
            ->add('message_line_2_important', CheckboxType::class,  array('required' => false, 'mapped' => false, 'label' => 'event.form.highlight'))
            ->add('message_line_3', TextType::class, array('attr' => array('maxlength ' => '65', 'autocapitalize' => 'off'), 'label' => 'event.form.message_line_3'))
            ->add('message_line_3_important', CheckboxType::class,  array('required' => false, 'mapped' => false, 'label' => 'event.form.highlight'))
            ->add('startTime', TimeType::class,  array('mapped' => false, 'widget' => 'single_text', 'required' => true, 'label' => 'event.form.start_time'))
            ->add('save', SubmitType::class, array('label' => 'event.form.submit'))
            ->getForm();
            
            $form->handleRequest($request);
            
            if ($form->isSubmitted() && $form->isValid() && $publisher->getRemainingMessages() > 0 && (is_null($publisher->getLastPublicationDate()) || $publisher->getMinutesSinceLastPublication() >= 120)) {
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
                $message->setPublisher($publisher);
                
                if ($event->getModerated()) {
                    $message->setValidationPending(1);
                    
                    $data = array();
                    $data['push_type'] = 'validation';
                    $data['push_topic'] = 'validation';
                    $data['message'] = $this->renderView('admin/templates/notification.template.html.twig', ['message' => $message]);
                    
                    $context = new \ZMQContext();
                    $socket = $context->getSocket(\ZMQ::SOCKET_PUSH, 'onNewMessage');
                    $socket->connect("tcp://localhost:5555");
                    $socket->send(json_encode($data));
                    $socket->disconnect("tcp://localhost:5555");
                }
                
                $em->persist($message);
                
                $publisher->setLastPublicationDate(new \DateTime('now'));
                $publisher->setRemainingMessages($publisher->getRemainingMessages() - 1);
                $em->persist($publisher);
                
                $em->flush();
                $success = true;
            }
            
            return $this->render('event/post.html.twig', array(
                'form' => $form->createView(),
                'event' => $event,
                'publisher' => $publisher,
                'success' => $success,
                'closed' => false
            ));
        }

    }
    
    /**
     * @Route("/event/{eventID}/brewery/taplist/", name="brewery_taplist")
     */
    public function manager_taplist($eventID, Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $event = $em->getRepository('\App\Entity\Event\Event')->find($eventID);
        
        $success = false;
        
        if (is_null($event)) {
            $event = $em->getRepository('\App\Entity\Event\Event')->findBySlug($eventID)[0];
            if (is_null($event)) {
                throw $this->createNotFoundException('Unknown event');
            }
        }
        
        $localBeer = new LocalBeer();
        $form = $this->createForm(LocalBeerType::class, $localBeer);
        $form->handleRequest($request);
        
        $session = $request->getSession();
            
        if (!$session->get('post_access_key/'.$eventID)) {
            return $this->redirectToRoute('brewery_portal', array('eventID' => ($event->getSlug()?$event->getSlug():$event->getId())));
        } else {
            $authKey = $session->get('post_access_key/'.$eventID);
            $publisher = $em->getRepository('\App\Entity\Event\Publisher')->findOneBy(array('access_key' => $authKey, 'event' => $event));
            if (!$publisher) {
                return $this->redirectToRoute('post_logout', array('eventID' => ($event->getSlug()?$event->getSlug():$event->getId())));
            }
            
            if ($form->isSubmitted() && $form->isValid()) {
                $localBeer->setOwner($publisher);
                $em->persist($localBeer);
                $em->flush();
                $success = true;
            }
            
            $tapListItems = $em->getRepository('\App\Entity\Event\TapListItem')->getEventTapList($event, $publisher);
                        
            return $this->render('event/brewery_taplist.html.twig', array(
                'event' => $event,
                'publisher' => $publisher,
                'tapListItems' => $tapListItems,
                'success' => $success,
                'local_beer' => $localBeer,
                'form' => $form->createView(),
                'local_mode' => $form->isSubmitted(),
            ));
        }
        
    }
    
    /**
     * @Route("/event/{eventID}/brewery/logout/", name="post_logout")
     */
    public function post_logout($eventID, Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $event = $em->getRepository('\App\Entity\Event\Event')->find($eventID);
        
        if (is_null($event)) {
            $event = $em->getRepository('\App\Entity\Event\Event')->findBySlug($eventID)[0];
            if (is_null($event)) {
                throw $this->createNotFoundException('Unknown event');
            }
        }
        
        $session = $request->getSession();
        $session->remove('post_access_key/'.$eventID);
        return $this->redirectToRoute('brewery_portal', array('eventID' => ($event->getSlug()?$event->getSlug():$event->getId())));
    }
    
    /**
     * @Route("/event/{eventID}/publishers/", name="post_mass_create_publishers")
     */
    public function post_mass_create_publishers($eventID, Request $request)
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        
        $success = false;
        $em = $this->getDoctrine()->getManager();
        $event = $em->getRepository('\App\Entity\Event\Event')->find($eventID);
        
        if (is_null($event)) {
            $event = $em->getRepository('\App\Entity\Event\Event')->findBySlug($eventID)[0];
            if (is_null($event)) {
                throw $this->createNotFoundException('Unknown event');
            }
        }
        
        $form = $this->createFormBuilder()
        ->add('publishers_list', TextareaType::class, array('required' => true, 'label' => 'event.form.publishers'))
        ->add('send', SubmitType::class, array('label' => 'event.form.register'))
        ->getForm();
        
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            foreach (explode("\r\n", $data['publishers_list']) as $publisherData) {
                $dataArray = explode("|", $publisherData);
                if (!$publisher = $em->getRepository('\App\Entity\Event\Publisher')->findOneBy(array('name' => trim($dataArray[0])))) {
                    $publisher = new Publisher();
                    $publisher->setName(trim($dataArray[0]));
                }
                if (count($dataArray) > 1 && filter_var($dataArray[1], FILTER_VALIDATE_EMAIL)) {
                    $publisher->setEmail($dataArray[1]);
                    if (count($dataArray) > 2 && ($dataArray[2] == "fr" || $dataArray[2] == "en")) {
                        $publisher->setLanguage($dataArray[2]);
                    }
                }
                $publisher->setEvent($event);
                $em->persist($publisher);
            }
            $em->flush();
            $success = true;
        }
        
        return $this->render('event/create_publishers.html.twig', array(
            'form' => $form->createView(),
            'event' => $event,
            'success' => $success
        ));
    }
}
