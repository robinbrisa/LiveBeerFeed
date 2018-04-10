<?php
namespace App\Command;


use App\Server\Notification;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class EventNotificationServerCommand extends ContainerAwareCommand
{   
    /**
     * Configure a new Command Line
     */
    protected function configure()
    {
        $this
        ->setName('event:notification:server')
        ->setDescription('Start the notification server.');
    }
    
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        
        $loop   = \React\EventLoop\Factory::create();
        $pusher = new \App\Server\Notification;
        
        // Listen for the web server to make a ZeroMQ push after an ajax request
        $context = new \React\ZMQ\Context($loop);
        $pull = $context->getSocket(\ZMQ::SOCKET_PULL);
        $pull->bind('tcp://127.0.0.1:5555'); // Binding to 127.0.0.1 means the only client that can connect is itself
        $pull->on('message', array($pusher, 'onNewMessage'));
        
        // Set up our WebSocket server for clients wanting real-time updates
        $webSock = new \React\Socket\Server('0.0.0.0:8080', $loop); // Binding to 0.0.0.0 means remotes can connect
        $webServer = new \Ratchet\Server\IoServer(
            new \Ratchet\Http\HttpServer(
                new \Ratchet\WebSocket\WsServer(
                    new \Ratchet\Wamp\WampServer(
                        $pusher
                        )
                    )
                ),
            $webSock
            );
        
        echo "Started\n";
        $loop->run();
    }
    
}

