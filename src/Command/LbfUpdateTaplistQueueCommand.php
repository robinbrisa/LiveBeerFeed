<?php

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Style\SymfonyStyle;
use Doctrine\ORM\EntityManagerInterface;
use App\Service\Tools;
use Symfony\Component\Console\Input\ArrayInput;
use App\Entity\Event\TapListQueue;
use App\Service\UntappdAPI;
use App\Service\UntappdAPISerializer;

class LbfUpdateTaplistQueueCommand extends Command
{
    protected static $defaultName = 'lbf:update:taplist:queue';

    protected function configure()
    {
        $this
            ->setDescription('Gets info for beers in taplist queues')
        ;
    }
    
    public function __construct(EntityManagerInterface $em, Tools $tools, UntappdAPI $untappdAPI, UntappdAPISerializer $untappdAPISerializer)
    {
        $this->untappdAPI = $untappdAPI;
        $this->untappdAPISerializer = $untappdAPISerializer;
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
                
                $apiKey = $this->tools->getBestAPIKey($apiKeyPool);
                
                if ($apiKey === false) {
                    $output->writeln(sprintf('[%s] No more API keys available', date('H:i:s')));
                    return false;
                }
                $rateLimitRemaining = 0;
                $i = 0;
                
                foreach ($queue as $queueElement) {
                    $session = $queueElement->getSession();
                    $i++;
                    $output->writeln(sprintf('[%s] Adding beer %d', date('H:i:s'), $queueElement->getUntappdId()));
                    if ($response = $this->untappdAPI->getBeerInfo($queueElement->getUntappdId(), $apiKey)) {
                        $rateLimitRemaining = $response->headers['X-Ratelimit-Remaining'];
                        if ($response == "DELETED") {
                            $this->em->remove($queueElement);
                            $this->em->flush();
                            $output->writeln(sprintf('[%s] Beer has been deleted from Untappd, removing from queue', date('H:i:s')));
                        } else {
                            $beerData = $response->body->response->beer;
                            $beer = $this->untappdAPISerializer->handleBeerObject($beerData);
                            $output->writeln(sprintf('[%s] Beer %s has been created/updated', date('H:i:s'), $beer->getName()));
                            if ($beer) {
                                if (!$session->getBeers()->contains($beer)) {
                                    $session->addBeer($beer);
                                    $this->em->persist($session);
                                } else {
                                    $output->writeln(sprintf('[%s] Beer was already in taplist', date('H:i:s')));
                                }
                                $this->em->remove($queueElement);
                                $this->em->flush();
                                $output->writeln(sprintf('[%s] Beer has been moved from queue to taplist', date('H:i:s')));
                            } else {
                                $output->writeln(sprintf('[%s] Couldn\'t find the created beer', date('H:i:s')));
                            }
                            $output->writeln(sprintf('[%s] %d beers left in taplist queue', date('H:i:s'), count($queue) - $i));
                        }
                    } else {
                        $output->writeln(sprintf('[%s] Couldn\'t get beer information', date('H:i:s')));
                    }
                    $poolKey = $apiKey;
                    if (is_null($apiKey)) {
                        $poolKey = 'default';
                    }
                    $apiKeyPool[$poolKey] = $rateLimitRemaining;
                    $apiKey = $this->tools->getBestAPIKey($apiKeyPool);
                    if ($apiKey === false) {
                        $output->writeln(sprintf('[%s] Out of API keys', date('H:i:s')));
                        break;
                    }
                }
            }
        }
        unset($queue);
    }
}
