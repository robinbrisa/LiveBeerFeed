<?php
// src/Service/Tools.php
namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;
use Minishlink\WebPush\WebPush;
use Minishlink\WebPush\Subscription;

class PushNotification
{
    private $em;
    private $push_private_key;
    
    public function __construct($pushPrivateKey, EntityManagerInterface $em)
    {
        $this->em = $em;
        $this->em->getConnection()->getConfiguration()->setSQLLogger(null);
        $this->push_private_key = $pushPrivateKey;
    }
    
    public function pushNotification($subscribers, $title, $message, $icon, $additionnalData = null) {
        $notifications = array();
        foreach ($subscribers as $subscriber) {
            $subscriptionArray = array('endpoint' => $subscriber->getEndpoint());
            if ($publicKey = $subscriber->getPublicKey()) {
                $subscriptionArray['publicKey'] = $publicKey;
            }
            if ($authToken = $subscriber->getAuthToken()) {
                $subscriptionArray['authToken'] = $authToken;
            }
            if ($contentEncoding = $subscriber->getContentEncoding()) {
                $subscriptionArray['contentEncoding'] = $contentEncoding;
            }
            $notifications[] = array('subscription' => Subscription::create($subscriptionArray));
        }
        
        $payload = array(
            'title' => $title,
            'message' => $message,
            'more' => $additionnalData
        );
        if ($icon) {
            $payload['icon'] = '/images/events/notification/' . $icon;
        }
        
        $auth = array(
            'VAPID' => array(
                'subject' => 'livebeerfeed.com',
                'publicKey' => 'BPcHVWxT9OtIubNDDePH2yP6QaNRJ3JvbLAMXPGt-FigOR5i8Yl5fomNN6ZHDTG67EQIAaDGnRZQeAZW1NuuElQ',
                'privateKey' => $this->push_private_key,
            ),
        );
                
        $webPush = new WebPush($auth);
        foreach ($notifications as $notification) {
            $webPush->sendNotification($notification['subscription'], json_encode($payload), false, ['TTL' => 3600]);
        }
        $webPush->flush();
        
        unset($webPush);
        unset($notifications);
        unset($subscribers);
    }
    
}