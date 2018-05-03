<?php

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Doctrine\ORM\EntityManagerInterface;
use App\Service\Tools;
use Symfony\Component\Console\Input\ArrayInput;
use App\Entity\Event\TapListQueue;

class LbfUpdateTaplistQueueCommand extends Command
{
    protected static $defaultName = 'lbf:update:taplist:queue';

    protected function configure()
    {
        $this
            ->setDescription('Gets info for beers in taplist queues')
        ;
    }
    
    public function __construct(EntityManagerInterface $em, Tools $tools)
    {
        $this->em = $em;
        $this->em->getConnection()->getConfiguration()->setSQLLogger(null);
        $this->tools = $tools;
        
        parent::__construct();
    }
    
    protected function execute(InputInterface $input, OutputInterface $output)
    {        
        $queue = $this->em->getRepository('App\Entity\Event\TapListQueue')->findAll();
        if (count($queue) > 0) {
            $output->writeln(sprintf('[%s] %d beers left in taplist queue', date('H:i:s'), count($queue)));
            $apiKeyPool = $this->tools->getAPIKeysPool();
            if (array_sum($apiKeyPool) < 40 && $this->em->getRepository('\App\Entity\Event\Event')->findCurrentEvents()) {
                $output->writeln(sprintf('[%s] API Key pool is too low (%d), events are running.', date('H:i:s'), array_sum($apiKeyPool)));
            } elseif (array_sum($apiKeyPool) < 5) {
                $output->writeln(sprintf('[%s] API Key pool is too low (%d)', date('H:i:s'), array_sum($apiKeyPool)));
            } else {
                $beerInfoCommand = $this->getApplication()->find('untappd:get:beer:info');
                $output->writeln(sprintf('[%s] Adding beer %d', date('H:i:s'), $queue[0]->getUntappdId()));
                $arguments = array(
                    'command' => 'untappd:get:beer:info',
                    'id'    => $queue[0]->getUntappdId(),
                    '-e'  => 'prod',
                    '--no-debug'  => true,
                );
                
                $session = $queue[0]->getSession();
                
                $beerInfoCommand->run(new ArrayInput($arguments), $output);
                $beer = $this->em->getRepository('App\Entity\Beer\Beer')->find($queue[0]->getUntappdId());
                if ($beer) {
                    if (!$session->getBeers()->contains($beer)) {
                        $session->addBeer($beer);
                        $this->em->persist($session);
                    } else {
                        $output->writeln(sprintf('[%s] Beer was already in taplist', date('H:i:s')));
                    }
                    $this->em->remove($queue[0]);
                    $this->em->flush();
                    $output->writeln(sprintf('[%s] Beer has been moved from queue to taplist', date('H:i:s')));
                } else {
                    $output->writeln(sprintf('[%s] Couldn\'t find the created beer', date('H:i:s')));
                }
            }
        }
        unset($queue);
    }
}
