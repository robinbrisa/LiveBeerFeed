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
use App\Entity\Search\Query;
use App\Entity\Search\Element;
use App\Entity\Event\TapListQueue;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class ToolsController extends Controller
{
    /**
     * @Route("/tools/search/", name="search_beers")
     */
    public function search(Request $request)
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        
        $success = false;
        $em = $this->getDoctrine()->getManager();
        
        $form = $this->createFormBuilder()
        ->add('beers_list', TextareaType::class, array('required' => true, 'label' => 'tools.form.beers'))
        ->add('send', SubmitType::class, array('label' => 'event.form.register'))
        ->getForm();
        
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $query = new Query();
            $query->setFinished(0);
            foreach (explode("\r\n", $data['beers_list']) as $beerData) {
                $element = new Element();
                $element->setSearchString($beerData);
                $element->setQuery($query);
                $element->setFinished(0);
                $em->persist($element);
            }
            $em->persist($query);
            $em->flush();
            $success = true;
        }
        
        return $this->render('tools/search_beers.html.twig', array(
            'form' => $form->createView(),
            'success' => $success
        ));
    }
    
    /**
     * @Route("/tools/search/results/{id}", name="search_results")
     */
    public function results($id, Request $request)
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        
        $success = false;
        $em = $this->getDoctrine()->getManager();
        
        $query = $em->getRepository('App\Entity\Search\Query')->find($id);
        if (!$query) {
            $this->createNotFoundException('This search query is unknown');
        }
        
        $upcomingSessions = $em->getRepository('\App\Entity\Event\Session')->findUpcomingSessions();
        if (!is_null($upcomingSessions)) {
            $arraySelect = array();
            foreach ($upcomingSessions as $session) {
                $arraySelect[$session->getName()] = $session->getId();
            }
        }
        
        $form = $this->createFormBuilder()
        ->add('session_select', ChoiceType::class, array('required' => true, 'choices' => $arraySelect))
        ->add('send', SubmitType::class, array('label' => 'event.form.register'))
        ->getForm();
        
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $session = $em->getRepository('\App\Entity\Event\Session')->find($data['session_select']);
            $selectedResults = $em->getRepository('\App\Entity\Search\Result')->findSelectedResults($query);
            foreach ($selectedResults as $result) {
                if (!$em->getRepository('\App\Entity\Event\TapListQueue')->findOneBy(array('session' => $session, 'untappdID' => $result->getBeer()->getId()))) {
                    $tapListQueueElement = new TapListQueue();
                    $tapListQueueElement->setSession($session);
                    $tapListQueueElement->setUntappdID($result->getBeer()->getId());
                    $em->persist($tapListQueueElement);
                    $em->flush();
                }
            }
            $success = true;
        }
        
        return $this->render('tools/search_results.html.twig', array(
            'form' => $form->createView(),
            'query' => $query,
            'success' => $success
        ));
    }
    
}
