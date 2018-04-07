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
        foreach ($events as $event) {
            $output->writeln(sprintf('Found event "' . $event->getName() . '"'));
            foreach ($event->getVenues() as $venue) {
                $output->writeln(sprintf('Refreshing venue "' . $venue->getName() . '"'));
                $arguments = array(
                    'command' => 'untappd:get:venue:history',
                    'vid'    => $venue->getId(),
                    '--update' => true
                );
                $checkinCommand->run(new ArrayInput($arguments), $output);
            }
        }
        $io->success('All current events are now refreshed');
    }
}
