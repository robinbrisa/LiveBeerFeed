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

class UntappdGetUserinfoCommand extends Command
{
    protected static $defaultName = 'untappd:get:user:info';
    private $untappdAPI;
    private $untappdAPISerializer;
    
    protected function configure()
    {
        $this
            ->setDescription('Gets user information and stores it into the database')
            ->addArgument('username', InputArgument::REQUIRED, 'The username');
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
        $username = $input->getArgument('username');
        
        if ($response = $this->untappdAPI->getUserInfo($username)) {
            $output->writeln(sprintf('Successfully received user information.'));
            $userData = $response->body->response->user;
            $this->untappdAPISerializer->handleUserObject($userData);
            $io->success('User ' . $username . ' has been created/updated.');
        } else {
            $output->writeln(sprintf('Couldn\'t get user information.'));
        }
    }
}
