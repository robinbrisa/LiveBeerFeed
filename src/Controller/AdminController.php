<?php

namespace App\Controller;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\Event;

class AdminController extends Controller
{
    /**
     * @Route("/admin", name="homepage")
     */
    public function index()
    {
        $em = $this->getDoctrine()->getManager();
        $events = $em->getRepository('\App\Entity\Event')->findCurrentEvents();
        
        return $this->render('main/index.html.twig', [
            'currentEvents' => $events,
        ]);
    }
    
    /**
     * @Route("/admin/event/add", name="admin_event_add")
     */
    public function adminEventAdd(Request $request)
    {
        $event = new Event();
        $event->setStartDate(new \DateTime('today'));
        $event->setEndDate(new \DateTime('tomorrow'));
        
        $success = false;
        
        $form = $this->createFormBuilder($event)
            ->add('name', TextType::class)
            ->add('startDate', DateTimeType::class)
            ->add('endDate', DateTimeType::class)
            ->add('save', SubmitType::class, array('label' => 'Add event'))
            ->getForm();
        
            
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            // $form->getData() holds the submitted values
            // but, the original `$task` variable has also been updated
            $event = $form->getData();
            
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($event);
            $entityManager->flush();
            $success = true;
        }
            
        return $this->render('admin/event/add.html.twig', [
            'form' => $form->createView(),
            'success' => $success
        ]);
    }
    
}
