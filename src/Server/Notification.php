<?php
namespace App\Server;
use Ratchet\ConnectionInterface;
use Ratchet\Wamp\WampServerInterface;

class Notification implements WampServerInterface {
    /**
     * A lookup of all the topics clients have subscribed to
     */
    protected $subscribedLives = array();
    
    public function onSubscribe(ConnectionInterface $conn, $topic) {
        $this->subscribedLives[$topic->getId()] = $topic;
        echo "Client has been subscribed to topic " . $topic->getId() . "\n";
    }
    
    public function onUnSubscribe(ConnectionInterface $conn, $live) {
    }
    
    /**
     * @param string JSON'ified string we'll receive from ZeroMQ
     */
    public function onNewMessage($entry) {        
        $entryData = json_decode($entry, true);
        
        // If the lookup topic object isn't set there is no one to publish to
        if (!array_key_exists('push_topic', $entryData) || !array_key_exists($entryData['push_topic'], $this->subscribedLives)) {
            return;
        }
        
        if ($entryData['push_type'] == "checkins") {
            echo "Got " . $entryData['count'] . " checkins for " . $entryData['type'] . " #" . $entryData['id'] . "\n";
        }
        
        $topic = $this->subscribedLives[$entryData['push_topic']];
        
        if ($entryData['push_type'] == "checkins") {
            if ($entryData['count'] > 0) {
                $topic->broadcast($entryData);
            }
        } else {
            $topic->broadcast($entryData);
        }
    }
    
    public function onOpen(ConnectionInterface $conn) {
        echo "New client connected\n";
    }
    
    public function onClose(ConnectionInterface $conn) {
    }
    public function onCall(ConnectionInterface $conn, $id, $topic, array $params) {
        // In this application if clients send data it's because the user hacked around in console
        $conn->callError($id, $topic, 'You are not allowed to make calls')->close();
    }
    public function onPublish(ConnectionInterface $conn, $topic, $event, array $exclude, array $eligible) {
        // In this application if clients send data it's because the user hacked around in console
        $conn->close();
    }
    public function onError(ConnectionInterface $conn, \Exception $e) {
    }
}