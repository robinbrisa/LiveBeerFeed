<?php

namespace App\Controller;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class UserController extends Controller
{
    /**
     * @Route("/user/{username}", name="profile")
     */
    public function index($username)
    {
        $em = $this->getDoctrine()->getManager();
        $stats = array();
        $user = $em->getRepository('\App\Entity\User\User')->findOneBy(array('user_name' => $username));
        $stats['toasts_received'] = $em->getRepository('\App\Entity\Checkin\Toast')->getTotalToastsToUser($user);
        $stats['toasts_done'] = $em->getRepository('\App\Entity\Checkin\Toast')->getTotalToastsByUser($user);
        
        return $this->render('user/index.html.twig', [
            'user' => $user,
            'stats' => $stats
        ]);
    }
}
