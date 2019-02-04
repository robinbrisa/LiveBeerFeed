<?php

namespace App\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Wrep\Daemonizable\Command\EndlessContainerAwareCommand;
use Symfony\Component\Console\Input\ArrayInput;
use App\Service\EventStats;
use Symfony\Component\Translation\TranslatorInterface;
use Doctrine\ORM\EntityManagerInterface;

class LiveDaemonStartCommand extends EndlessContainerAwareCommand
{
    protected static $defaultName = 'live:daemon:start';
    
    protected function configure()
    {
        $this
            ->setDescription('Start the daemon to send push notifications for lives')
            ->setTimeout(5);
        ;
    }
    
    public function __construct(EventStats $stats,TranslatorInterface $translator, EntityManagerInterface $em, \Twig_Environment $templating)
    {
        $this->em = $em;
        $this->em->getConnection()->getConfiguration()->setSQLLogger(null);
        $this->stats = $stats;
        $this->translator = $translator;
        $this->last_checkins_push = new \DateTime();
        $this->templating = $templating;
        
        $this->push_info_command_args = array(
            'command' => 'live:push:info',
            '-e'  => 'prod',
            '--no-debug'  => true,
        );
        
        $this->refresh_events_command_args = array(
            'command' => 'lbf:refresh:events',
            '-e'  => 'prod',
            '--no-debug'  => true,
        );
        
        $this->get_beer_info_command_args = array(
            'command' => 'untappd:get:beer:info',
            '-e'  => 'prod',
            '--no-debug'  => true,
        );
        
        parent::__construct();
    }
    
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln(sprintf('[%s] ---- Iteration start ----', date('H:i:s')));
        
        $push_info_command = $this->getApplication()->find('live:push:info');
        $refresh_events_command = $this->getApplication()->find('lbf:refresh:events');
        $get_beer_info_command = $this->getApplication()->find('untappd:get:beer:info');
        
        $push_info_command->run(new ArrayInput($this->push_info_command_args), $output);
      
        if ($this->last_checkins_push->diff(New \DateTime())->i >= 1) {
            $refresh_events_command->run(new ArrayInput($this->refresh_events_command_args), $output);
            $this->last_checkins_push = new \DateTime();
        }
        
        $beersToUpdate = $this->em->getRepository('App\Entity\Beer\Beer')->findBy(array('needs_refresh' => 1));
        $notificationArray = array();
        $notifiedBeerCount = 0;
        foreach ($beersToUpdate as $beer) {
            $output->writeln(sprintf('[%s] Updating beer %s (%d)', date('H:i:s'), $beer->getName(), $beer->getId()));
            $this->get_beer_info_command_args['id'] = $beer->getId();
            $get_beer_info_command->run(new ArrayInput($this->get_beer_info_command_args), $output);
            $beer->setNeedsRefresh(0);
            $beerEvents = $this->em->getRepository('\App\Entity\Event\Event')->getFutureOrCurrentEventsWhereBeerIsAvailable($beer);
            foreach ($beerEvents as $beerEvent) {
                $beerSessions = $this->em->getRepository('\App\Entity\Event\Session')->getEventSessionsWhereBeerIsAvailable($beerEvent, $beer);
                if (!array_key_exists($beerEvent->getId(), $notificationArray)) {
                    $notificationArray[$beerEvent->getId()] = array(); 
                }
                foreach($beerSessions as $beerSession) {
                    if (!array_key_exists($beerSession->getId(), $notificationArray[$beerEvent->getId()])) {
                        $notificationArray[$beerEvent->getId()][$beerSession->getId()] = array();
                    }
                    $notificationArray[$beerEvent->getId()][$beerSession->getId()][$beer->getId()] = $this->templating->render('taplist/templates/beer.template.html.twig', ['beer' => $beer, 'session' => $beerSession]);
                    $notifiedBeerCount++;
                }
            }
            $this->em->persist($beer);
        }
        foreach($notificationArray as $eventToNotify => $beersContent) {
            $pushData = array(
                'push_type' => 'beer_update',
                'push_topic' => 'taplist-'.$eventToNotify.'-all',
                'beers' => $notificationArray[$eventToNotify],
                'count' => $notifiedBeerCount
            );
            $context = new \ZMQContext();
            $socket = $context->getSocket(\ZMQ::SOCKET_PUSH, 'onNewMessage');
            $socket->connect("tcp://localhost:5555");
            $socket->send(json_encode($pushData));
            $socket->disconnect("tcp://localhost:5555");
        }
        $this->em->flush();
        unset($beer);
        unset($beersToUpdate);
        
        $output->writeln(sprintf('[%s] ----- Iteration end -----', date('H:i:s')));
    }
}
