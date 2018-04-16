<?php

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Doctrine\ORM\EntityManagerInterface;
use App\Service\EventStats;

class LivePushStatsCommand extends Command
{
    protected static $defaultName = 'live:push:stats';

    protected function configure()
    {
        $this->setDescription('Pushes up to date stats to visitors for current events')
        ->addArgument('id', InputArgument::REQUIRED, 'The event/venue ID');
    }
    
    public function __construct(EntityManagerInterface $em, EventStats $stats)
    {
        $this->em = $em;
        $this->em->getConnection()->getConfiguration()->setSQLLogger(null);
        $this->stats = $stats;
        
        parent::__construct();
    }
    
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        
        $id = $input->getArgument('id');
        
        if (!$event = $this->em->getRepository('\App\Entity\Event\Event')->find($id)) {
            throw New \Exception("Event does not exist");
        }
        
        $statsArray = array('push_type' => 'stats', 'push_topic' => 'stats-'.$id, 'stats' => array(), 'id' => $id);
        
        $statsArray['stats'] = $this->stats->getStatsCards($event, true);
                
        $context = new \ZMQContext();
        $socket = $context->getSocket(\ZMQ::SOCKET_PUSH, 'onNewMessage');
        $socket->connect("tcp://localhost:5555");
        $socket->send(json_encode($statsArray));
        $socket->disconnect("tcp://localhost:5555");
        $output->writeln(sprintf('[%s] New stats have been pushed', date('H:i:s')));
    }
}
