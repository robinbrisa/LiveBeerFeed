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
    public function results($id)
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        
        $em = $this->getDoctrine()->getManager();
        
        $query = $em->getRepository('App\Entity\Search\Query')->find($id);
        if (!$query) {
            $this->createNotFoundException('This search query is unknown');
        }
        
        return $this->render('tools/search_results.html.twig', array(
            'query' => $query,
        ));
    }
    
}
