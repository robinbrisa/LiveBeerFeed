<?php

namespace App\Controller;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use App\Service\UntappdAPI;
use App\Service\UntappdAPISerializer;

class UntappdAPIController extends Controller
{
    /**
     * @Route("/get/user/info/{username}", name="UserInfo")
     */
    public function get_user_info($username, UntappdAPI $untappdAPI, UntappdAPISerializer $untappdAPISerializer)
    {
        $response = $untappdAPI->getUserInfo($username)->body;
        $user = $untappdAPISerializer->handleUserObject($response->response->user);
                
        return $this->json([
            'status' => 'SUCCESS',
            'name' => $user->getUserName()
        ]);
    }
    
    /**
     * @Route("/get/brewery/info/{id}", name="BreweryInfo")
     */
    public function get_brewery_info($id, UntappdAPI $untappdAPI, UntappdAPISerializer $untappdAPISerializer)
    {
        $response = $untappdAPI->getBreweryInfo($id)->body;
        $brewery = $untappdAPISerializer->handleBreweryObject($response->response->brewery);
        
        return $this->json([
            'status' => 'SUCCESS',
            'name' => $brewery->getName()
        ]);
    }
    
    /**
     * @Route("/get/beer/info/{id}", name="BeerInfo")
     */
    public function get_beer_info($id, UntappdAPI $untappdAPI, UntappdAPISerializer $untappdAPISerializer)
    {
        $response = $untappdAPI->getBeerInfo($id)->body;
        $beer = $untappdAPISerializer->handleBeerObject($response->response->beer);
                
        return $this->json([
            'status' => 'SUCCESS',
            'id' => $beer->getId(),
            'name' => $beer->getName()
        ]);
    }
}
