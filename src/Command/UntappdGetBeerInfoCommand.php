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
use App\Service\Tools;

class UntappdGetBeerInfoCommand extends Command
{
    protected static $defaultName = 'untappd:get:beer:info';
    private $untappdAPI;
    private $untappdAPISerializer;
    
    protected function configure()
    {
        $this
        ->setDescription('Gets beer information and stores it into the database')
        ->addArgument('id', InputArgument::REQUIRED, 'The beer ID');
        ;
    }
    
    public function __construct(UntappdAPI $untappdAPI, UntappdAPISerializer $untappdAPISerializer, Tools $tools) {
        $this->untappdAPI = $untappdAPI;
        $this->untappdAPISerializer = $untappdAPISerializer;
        $this->tools = $tools;
        
        parent::__construct();
    }
    
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $id = $input->getArgument('id');
        
        $apiKeyPool = $this->tools->getAPIKeysPool();
        $apiKey = $this->tools->getBestAPIKey($apiKeyPool);
        
        if ($apiKey === false) {
            $output->writeln(sprintf('[%s] No more API keys available', date('H:i:s')));
            return false;
        }
                
        if ($response = $this->untappdAPI->getBeerInfo($id, $apiKey)) {
            $output->writeln(sprintf('[%s] Successfully received beer information', date('H:i:s')));
            $beerData = $response->body->response->beer;
            $beer = $this->untappdAPISerializer->handleBeerObject($beerData);
            $output->writeln(sprintf('[%s] Beer %s has been created/updated', date('H:i:s'), $beer->getName()));
        } else {
            $output->writeln(sprintf('[%s] Couldn\'t get beer information', date('H:i:s')));
        }
    }
}
