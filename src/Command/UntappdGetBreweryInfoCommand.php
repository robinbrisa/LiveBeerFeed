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

class UntappdGetBreweryInfoCommand extends Command
{
    protected static $defaultName = 'untappd:get:brewery:info';
    private $untappdAPI;
    private $untappdAPISerializer;
    
    protected function configure()
    {
        $this
        ->setDescription('Gets user information and stores it into the database')
        ->addArgument('id', InputArgument::REQUIRED, 'The username');
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
        $id = $input->getArgument('id');
        
        if ($response = $this->untappdAPI->getBreweryInfo($id)) {
            $output->writeln(sprintf('Successfully received brewery information.'));
            $breweryData = $response->body->response->brewery;
            $brewery = $this->untappdAPISerializer->handleBreweryObject($breweryData);
            $io->success('Brewery ' . $brewery->getName() . ' has been created/updated.');
        } else {
            $output->writeln(sprintf('Couldn\'t get brewery information.'));
        }
    }
}
