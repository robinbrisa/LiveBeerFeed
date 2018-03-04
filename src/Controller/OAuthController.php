<?php

namespace App\Controller;

use Symfony\Component\Routing\Annotation\Route;
use App\Service\UntappdAPI;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;


class OAuthController extends Controller
{
    /**
     * @Route("/oauth/", name="OAuth Handler")
     */
    public function index(UntappdAPI $untappdAPI)
    {
        return $this->render('oauth/index.html.twig', array());
        
    }
    
    /**
     * @Route("/oauth/authorize", name="OAuth Authorize")
     */
    public function authorize(UntappdAPI $untappdAPI)
    {
        $authCode = $untappdAPI->authorize($_GET['code']);
        return $this->render('base.html.twig', array(
            'authCode' => $authCode
        ));
        
    }
}
