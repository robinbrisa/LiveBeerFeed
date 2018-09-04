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
use App\Entity\Search\Result;

class LbfProcessSearch extends Command
{
    protected static $defaultName = 'lbf:search:process';

    protected function configure()
    {
        $this
            ->setDescription('Processes search queries')
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
        $queue = $this->em->getRepository('App\Entity\Search\Query')->findPending();
        if (count($queue) > 0) {
            $output->writeln(sprintf('[%s] Pending queries : %d', date('H:i:s'), count($queue)));
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
                foreach ($queue as $query) {
                    $searchFail = false;
                    $output->writeln(sprintf('[%s] Processing query (ID %d)', date('H:i:s'), $query->getId()));
                    
                    foreach ($this->em->getRepository('App\Entity\Search\Element')->findPending($queue) as $element) {
                        $i++;
                        $output->writeln(sprintf('[%s] Searching for %s', date('H:i:s'), $element->getSearchString()));
                        if ($response = $this->untappdAPI->searchBeer($element->getSearchString(), $apiKey)) {
                            $rateLimitRemaining = $response->headers['X-Ratelimit-Remaining'];
                            $count = $response->body->response->beers->count;
                            $output->writeln(sprintf('[%s] Got %d results', date('H:i:s'), $count));
                            $found = $this->untappdAPISerializer->handleSearchResults($response->body->response->beers->items);
                            foreach ($found as $beer) {
                                $output->writeln(sprintf('[%s] Found %s (%s)', date('H:i:s'), $beer->getName(), $beer->getBrewery()->getName()));
                                $result = new Result();
                                $result->setBeer($beer);
                                $result->setSelected(0);
                                if ($count == 1) {
                                    $result->setSelected(1);
                                }
                                $result->setElement($element);
                                $this->em->persist($result);
                            }
                            $element->setFinished(1);
                            $this->em->persist($element);
                            $this->em->flush();
                        } else {
                            $output->writeln(sprintf('[%s] Search failed', date('H:i:s')));
                            $searchFail = true;
                        }
                        if (!$searchFail) {
                            $query->setFinished(1);
                            $this->em->persist($query);
                            $this->em->flush();
                        }
                    }
                 /* 
                    $poolKey = $apiKey;
                    if (is_null($apiKey)) {
                        $poolKey = 'default';
                    }
                    $apiKeyPool[$poolKey] = $rateLimitRemaining;
                    $apiKey = $this->tools->getBestAPIKey($apiKeyPool);
                    if ($apiKey === false) {
                        $output->writeln(sprintf('[%s] Out of API keys', date('H:i:s')));
                        break;
                    }*/
                }
            }
        }
        unset($queue);
    }
}
