<?php

namespace App\Controller;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
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
     * @Route("/admin/messages", name="validate_messages")
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
    
}