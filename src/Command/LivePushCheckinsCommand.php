<?php

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Input\ArrayInput;

class LivePushCheckinsCommand extends Command
{
    protected static $defaultName = 'live:push:checkins';

    protected function configure()
    {
        $this
        ->setDescription('Pushes new checkins to clients connected to live')
        ->addArgument('live_type', InputArgument::REQUIRED, 'The live type (event/venue)')
        ->addArgument('id', InputArgument::REQUIRED, 'The event/venue ID')
        ->addArgument('minID', InputArgument::OPTIONAL, 'Minimum ID of checkins to push')
        ;
    }
    
    public function __construct(EntityManagerInterface $em, \Twig_Environment $templating)
    {
        $this->em = $em;
        $this->em->getConnection()->getConfiguration()->setSQLLogger(null);
        $this->templating = $templating;
        
        parent::__construct();
    }
    
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $type = $input->getArgument('live_type');
        $id = $input->getArgument('id');
        $minID = $input->getArgument('minID');
                
        if ($type == "venue") {
            $checkins = $this->em->getRepository('App\Entity\Checkin\Checkin')->getVenueCheckins($id, $minID);
        } elseif ($type == "event") {
            $event = $this->em->getRepository('\App\Entity\Event\Event')->find($id);
            $checkins = $this->em->getRepository('App\Entity\Checkin\Checkin')->getVenueCheckins($event->getVenues(), $minID, null, $event->getStartDate());
        } else {
            throw New \Exception("Live type is invalid");
        }
        
        $checkinsArray = array('push_type' => 'checkins', 'push_topic' => 'checkins-'.$type.'-'.$id, 'checkins' => array(), 'medias' => array(), 'type' => $type, 'id' => $id);
        $mediaCount = 0;
        foreach ($checkins as $checkin) {
            if ($checkin->getMedias()[0]) {
                $mediaCount++;
                $checkinsArray['medias'][] = $this->templating->render('live/content/media.template.html.twig', ['checkin' => $checkin, 'i' => $mediaCount]);
            }
            $checkinsArray['checkins'][] = $this->templating->render('live/content/checkin.template.html.twig', ['checkin' => $checkin, 'i' => $mediaCount]);
            if ($event->getHasTaplist()) {
                $beer = $checkin->getBeer();
                $beer->setNeedsRefresh(1);
                $output->writeln(sprintf('[%s] Beer %s needs a refresh!', date('H:i:s'), $beer->getName()));
                $this->em->persist($beer);
            }
            unset($checkin);
        }
        $this->em->flush();
        unset($beer);
        $checkinsArray['mediaCount'] = count($checkinsArray['medias']);
        $checkinsArray['count'] = count($checkinsArray['checkins']);
        
        if ($checkinsArray['count'] > 0) {
            $context = new \ZMQContext();
            $socket = $context->getSocket(\ZMQ::SOCKET_PUSH, 'onNewMessage');
            $socket->connect("tcp://localhost:5555");
            $socket->send(json_encode($checkinsArray));
            $socket->disconnect("tcp://localhost:5555");
            $output->writeln(sprintf('[%s] New check-ins have been pushed', date('H:i:s')));
            
            $push_stats_command = $this->getApplication()->find('live:push:stats');
            $push_stats_command_args = array(
                'command' => 'live:push:stats',
                'id' => $id,
                '-e'  => 'prod',
                '--no-debug'  => true,
            );
            $push_stats_command->run(new ArrayInput($push_stats_command_args), $output);
        } else {
            $output->writeln(sprintf('[%s] No new check-ins to push', date('H:i:s')));
        }
    }
}
