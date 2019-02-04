<?php

namespace App\Controller;

use Symfony\Component\Routing\Annotation\Route;
use App\Service\UntappdAPI;
use App\Service\UntappdAPISerializer;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class OAuthController extends Controller
{
    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;
    
    /**
     * @Route("/oauth/", name="oauth_handler")
     */
    public function index(UntappdAPI $untappdAPI)
    {
        return $this->render('oauth/index.html.twig', array());
        
    }
    
    /**
     * @Route("/oauth/authorize", name="oauth_authorize")
     */
    public function authorize(UntappdAPI $untappdAPI, UntappdAPISerializer $untappdAPISerializer)
    {
        $access_token = $untappdAPI->authorize($_GET['code']);
        $response = $untappdAPI->getUserInfo(null, $access_token);
        $user = $untappdAPISerializer->handleUserObject($response->body->response->user, $access_token);
        
        $session = $this->get('session');
        $session->set('userUntappdID', $user->getId());
        return $this->redirect($session->getFlashBag()->get('lastURI')[0]);
    }
    
    /**
     * @Route("/oauth/logout", name="oauth_logout")
     */
    public function logout()
    {
        $session = $this->get('session');
        $session->remove('userUntappdID');
        return $this->redirect($session->getFlashBag()->get('lastURI')[0]);
    }
}
