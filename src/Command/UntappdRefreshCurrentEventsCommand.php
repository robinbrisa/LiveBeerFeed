<?php

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use App\Service\UntappdAPI;
use App\Service\UntappdAPISerializer;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Input\ArrayInput;

class UntappdRefreshCurrentEventsCommand extends Command
{
    protected static $defaultName = 'untappd:refresh:events';
    private $untappdAPI;
    private $untappdAPISerializer;
    private $em;
    
    protected function configure()
    {
        $this
            ->setDescription('Gets the list of events currently running and refreshes their checkins')
        ;
    }

    public function __construct(UntappdAPI $untappdAPI, UntappdAPISerializer $untappdAPISerializer, EntityManagerInterface $em)
    {
        $this->em = $em;
        $this->untappdAPI = $untappdAPI;
        $this->untappdAPISerializer = $untappdAPISerializer;
        
        parent::__construct();
    }
    
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        
        $events = $this->em->getRepository('\App\Entity\Event\Event')->findCurrentEvents();
        if (!$events) {
            throw New \Exception("No events are currently running");
        }
        
        $checkinCommand = $this->getApplication()->find('untappd:get:venue:history');
        $pushCommand = $this->getApplication()->find('live:push:checkins');
        foreach ($events as $event) {
            $output->writeln(sprintf('Found event %s', $event->getName()));
            $venues = $event->getVenues();
            
            $minID = null;
            $checkin = $this->em->getRepository('App\Entity\Checkin\Checkin')->getVenueCheckins($venues, null, 1);
            if (count($checkin) > 0) {
                $minID = $checkin[0]->getId();
            } 
            
            foreach ($venues as $venue) {
                $output->writeln(sprintf('Refreshing venue %s', $venue->getName()));
                $arguments = array(
                    'command' => 'untappd:get:venue:history',
                    'vid'    => $venue->getId(),
                    '--update' => true
                );
                $checkinCommand->run(new ArrayInput($arguments), $output);
            }
            
            $output->writeln(sprintf('Calling push command for event %s', $event->getName()));
            $arguments = array(
                'command' => 'live:push:checkins',
                'live_type' => 'event',
                'id' => $event->getId()
            );
            if ($minID) {
                $arguments['minID'] = $minID;
            }
            $pushCommand->run(new ArrayInput($arguments), $output);
        }
        $io->success('All current events are now refreshed');
    }
}
