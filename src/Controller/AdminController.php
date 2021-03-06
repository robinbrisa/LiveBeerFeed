<?php

namespace App\Controller;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Translation\TranslatorInterface;

class AdminController extends Controller
{
    /**
     * @Route("/admin/reloadClients", name="reload_clients")
     */
    public function reloadClientsAction(Request $request)
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        
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
    
    /**
     * @Route("/admin/validate", name="validate_messages")
     */
    public function validateMessagesAction(Request $request)
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        
        $em = $this->getDoctrine()->getManager();
        $messages = $em->getRepository('App\Entity\Event\Message')->findMessagesWaitingForValidation();
        
        return $this->render('admin/validate.html.twig', [
            'messages' => $messages,
        ]);
    }
    
    /**
     * @Route("/admin/validate/confirm", name="validate_messages_confirm")
     */
    public function confirmMessageAction(Request $request)
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        $em = $this->getDoctrine()->getManager();
        $request = Request::createFromGlobals();
        $message = $em->getRepository('App\Entity\Event\Message')->find($request->query->get('id'));
        if ($message) {
            if ($message->getStartDate() < new \DateTime('now')) {
                $message->setStartDate(new \DateTime('now'));
                $endDate = new \DateTime('now');
                $endDate->modify('+ 10 minutes');
                $message->setEndDate($endDate);
            }
            $message->setValidationPending(0);
            if ($request->query->get('no_broadcast')) {
                $message->setDoNotBroadcast(1);
            }
            $em->persist($message);
            $em->flush();
        } 
        
        if ($request->query->get('referer')) {
            return $this->redirect(urldecode($request->query->get('referer')));
        } else {
            return $this->redirectToRoute('validate_messages');
        }
    }
    
    /**
     * @Route("/admin/validate/decline", name="validate_messages_decline")
     */
    public function declineMessageAction(Request $request)
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        $em = $this->getDoctrine()->getManager();
        $request = Request::createFromGlobals();
        $message = $em->getRepository('App\Entity\Event\Message')->find($request->query->get('id'));
        if ($message) {
            $publisher = $message->getPublisher();
            $publisher->setLastPublicationDate(null);
            $publisher->setRemainingMessages($publisher->getRemainingMessages() + 1);
            $em->persist($publisher);
            $em->remove($message);
            $em->flush();
        }
        
        if ($request->query->get('referer')) {
            return $this->redirect(urldecode($request->query->get('referer')));
        } else {
            return $this->redirectToRoute('validate_messages');
        }
    }
    
    /**
     * @Route("/admin/notify_publishers", name="notify_publishers")
     */
    public function notifyPublishers(Request $request, \Swift_Mailer $mailer, TranslatorInterface $translator) {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        
        $em = $this->getDoctrine()->getManager();
        $publishers = $em->getRepository('\App\Entity\Event\Publisher')->findPublishersToNotifyByEmail();
        
        foreach ($publishers as $event) {
            foreach ($event as $email => $attachedPublishers) {
                if (count($attachedPublishers) > 1) {
                    $message = (new \Swift_Message($translator->trans('email.notify_title_batch', array('%event%' => $attachedPublishers[0]->getEvent()->getName()), null, $attachedPublishers[0]->getLanguage())))
                    ->setFrom(array('robin@livebeerfeed.com' => 'Live Beer Feed'))
                    ->setTo($email)
                    ->setBody(
                        $this->renderView(
                            'email/'.$attachedPublishers[0]->getLanguage().'/access_code_batch.html.twig',
                            array('publishers' => $attachedPublishers)
                            ),
                        'text/html'
                        )
                        /*
                         * If you also want to include a plaintext version of the message
                         ->addPart(
                         $this->renderView(
                         'emails/registration.txt.twig',
                         array('name' => $name)
                         ),
                         'text/plain'
                         )
                         */
                    ;
                    $mailer->send($message);
                    
                    foreach ($attachedPublishers as $publisher) {
                        $publisher->setNotified(true);
                        $em->persist($publisher);
                    }
                    $em->flush();
                } else {
                    $publisher = $attachedPublishers[0];
                    $message = (new \Swift_Message($translator->trans('email.notify_title', array('%event%' => $publisher->getEvent()->getName()), null, $publisher->getLanguage())))
                    ->setFrom(array('robin@livebeerfeed.com' => 'Live Beer Feed'))
                    ->setTo($publisher->getEmail())
                    ->setBody(
                        $this->renderView(
                            'email/'.$publisher->getLanguage().'/access_code.html.twig',
                            array('publisher' => $publisher)
                            ),
                        'text/html'
                        )
                        /*
                         * If you also want to include a plaintext version of the message
                         ->addPart(
                         $this->renderView(
                         'emails/registration.txt.twig',
                         array('name' => $name)
                         ),
                         'text/plain'
                         )
                         */
                    ;
                    $mailer->send($message);
                    
                    $publisher->setNotified(true);
                    $em->persist($publisher);
                    $em->flush();
                }
            }
            
            // redirect to the 'list' view of the given entity
            return $this->redirectToRoute('easyadmin', array(
                'action' => 'list',
                'entity' => 'Publisher',
            ));
        }
    }
    
    /**
     * @Route("/admin/remind_publishers/{eventID}", name="remind_publishers")
     */
    public function remindPublishers($eventID, Request $request, \Swift_Mailer $mailer, TranslatorInterface $translator) {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        
        $em = $this->getDoctrine()->getManager();
        $event = $em->getRepository('\App\Entity\Event\Event')->find($eventID);
        
        if (is_null($event)) {
            $event = $em->getRepository('\App\Entity\Event\Event')->findBySlug($eventID)[0];
            if (is_null($event)) {
                throw $this->createNotFoundException('Unknown event');
            }
        }
        
        $publishers = $em->getRepository('\App\Entity\Event\Publisher')->findPublishersToRemind($event);
        
        foreach ($publishers as $event) {
            foreach ($event as $email => $attachedPublishers) {
                if (count($attachedPublishers) > 1) {
                    $message = (new \Swift_Message($translator->trans('email.reminder_title_batch', array('%event%' => $attachedPublishers[0]->getEvent()->getName()), null, $attachedPublishers[0]->getLanguage())))
                    ->setFrom(array('robin@livebeerfeed.com' => 'Live Beer Feed'))
                    ->setTo($email)
                    ->setBody(
                        $this->renderView(
                            'email/'.$attachedPublishers[0]->getLanguage().'/taplist_reminder_batch.html.twig',
                            array('publishers' => $attachedPublishers)
                            ),
                        'text/html'
                        )
                    ;
                    $mailer->send($message);
                    
                    foreach ($attachedPublishers as $publisher) {
                        $publisher->setNotified(true);
                        $em->persist($publisher);
                    }
                    $em->flush();
                } else {
                    $publisher = $attachedPublishers[0];
                    $message = (new \Swift_Message($translator->trans('email.reminder_title', array('%event%' => $publisher->getEvent()->getName()), null, $publisher->getLanguage())))
                    ->setFrom(array('robin@livebeerfeed.com' => 'Live Beer Feed'))
                    ->setTo($publisher->getEmail())
                    ->setBody(
                        $this->renderView(
                            'email/'.$publisher->getLanguage().'/taplist_reminder.html.twig',
                            array('publisher' => $publisher)
                            ),
                        'text/html'
                        )
                    ;
                    $mailer->send($message);
                }
            }
            
            // redirect to the 'list' view of the given entity
            return $this->redirectToRoute('easyadmin', array(
                'action' => 'list',
                'entity' => 'Publisher',
            ));
        }
    }
    
    /**
     * @Route("/admin/login/{id}", name="force_untappd_login")
     */
    public function forceUntappdLogin($id)
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        $session = $this->get('session');
        $session->set('userUntappdID', $id);
        return $this->redirectToRoute('homepage');
    }
}