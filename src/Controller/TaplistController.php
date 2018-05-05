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

class TaplistController extends Controller
{
    /**
     * @Route("/taplist/{eventID}/", name="taplist")
     */
    public function index($eventID)
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
                
        $styles = $em->getRepository('\App\Entity\Beer\Style')->findAll();
        $styleCategories = array();
        foreach ($styles as $style) {
            if (!in_array($style->getCategory(), $styleCategories)) {
                $styleCategories[] = $style->getCategory();
            }
        }
                
        return $this->render('taplist/index.html.twig', [
            'event' => $event,
            'styleCategories' => $styleCategories
        ]);
    }
}
