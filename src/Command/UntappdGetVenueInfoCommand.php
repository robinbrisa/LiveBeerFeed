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

class UntappdGetVenueInfoCommand extends Command
{
    protected static $defaultName = 'untappd:get:venue:info';
    private $untappdAPI;
    private $untappdAPISerializer;
    
    protected function configure()
    {
        $this
            ->setDescription('Gets venue information and stores it into the database')
            ->addArgument('vid', InputArgument::REQUIRED, 'The venue ID');
        ;
    }

    public function __construct(UntappdAPI $untappdAPI, UntappdAPISerializer $untappdAPISerializer) {
        $this->untappdAPI = $untappdAPI;
        $this->untappdAPISerializer = $untappdAPISerializer;
        
        parent::__construct();
    }
    
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $vid = $input->getArgument('vid');
        
        if ($response = $this->untappdAPI->getVenueInfo($vid)) {
            $output->writeln(sprintf('Successfully received venue information.'));
            $venueData = $response->body->response->venue;
            $this->untappdAPISerializer->handleVenueObject($venueData);
            $io->success('Venue ' . $vid . ' has been created/updated.');
        } else {
            $output->writeln(sprintf('Couldn\'t get venue information.'));
        }
    }
}
