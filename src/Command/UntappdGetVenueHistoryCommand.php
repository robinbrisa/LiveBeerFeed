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
use App\Service\Tools;

class UntappdGetVenueHistoryCommand extends Command
{
    protected static $defaultName = 'untappd:get:venue:history';
    private $untappdAPI;
    private $untappdAPISerializer;
    private $em;
    
    protected function configure()
    {
        $this
            ->setDescription('Gets full venue history and stores it into the database')
            ->addArgument('vid', InputArgument::REQUIRED, 'The venue ID')
            ->addOption('update', 'u', InputOption::VALUE_NONE, 'Updates checkins to the latest synchronized')
            ->addOption('force', 'f', InputOption::VALUE_NONE, 'Force restart of the whole data collection');
        ;
    }

    public function __construct(UntappdAPI $untappdAPI, UntappdAPISerializer $untappdAPISerializer, EntityManagerInterface $em, Tools $tools)
    {
        $this->em = $em;
        $this->em->getConnection()->getConfiguration()->setSQLLogger(null);
        $this->untappdAPI = $untappdAPI;
        $this->untappdAPI->disableSqlLogger();
        $this->untappdAPISerializer = $untappdAPISerializer;
        $this->untappdAPISerializer->disableSqlLogger();
        $this->tools = $tools;
        
        parent::__construct();
    }
    
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $vid = $input->getArgument('vid');
        
        $venue = $this->em->getRepository('\App\Entity\Venue\Venue')->find($vid);
        if (!$venue) {
            throw New \Exception("Couldn't find this venue in the database");
        }
        
        if ($venue->getInternalFullHistoryGathered() && !$input->getOption('force') && !$input->getOption('update')) {
            throw New \Exception("History has already been gathered for this venue. Use --force to restart.");
        }
        
        if ($highestVenueCheckin = $this->em->getRepository('App\Entity\Checkin\Checkin')->getVenueCheckins($vid, null, 1)) {
            $highestVenueCheckin = $highestVenueCheckin[0];
        }
        $found = false;
        
        $apiKeyPool = $this->tools->getAPIKeysPool();
        $apiKey = $this->tools->getBestAPIKey($apiKeyPool);
        
        if ($apiKey === false) {
            $output->writeln(sprintf('[%s] No more API keys available', date('H:i:s')));
            return false;
        }
        
        $maxID = $venue->getInternalFullHistoryLastMaxId();
        if(!is_null($maxID) && !$input->getOption('force') && !$input->getOption('update')) {
            $output->writeln(sprintf('Restarting from checkin %d', $maxID));
        }
        if ($highestVenueCheckin && $input->getOption('update')) {
            $maxID = null;
            $output->writeln(sprintf('[%s] Update option applied, updating until checkin %d', date('H:i:s'), $highestVenueCheckin->getId()));
        }
        if ($input->getOption('force')) {
            $maxID = null;
            $output->writeln(sprintf('[%s] Force option applied, restarting full history', date('H:i:s')));
            $venue->setInternalFullHistoryGathered(false);
        }
        if ($response = $this->untappdAPI->getVenueCheckins($vid, $apiKey, $maxID, null, 25)) {
            $rateLimitRemaining = $response->headers['X-Ratelimit-Remaining'];
            while ($rateLimitRemaining == 0 && $apiKey !== false) {
                $poolKey = $apiKey;
                if (is_null($apiKey)) {
                    $poolKey = 'default';
                }
                $apiKeyPool[$poolKey] = intval($rateLimitRemaining);
                $apiKey = $this->tools->getBestAPIKey($apiKeyPool);
                $response = $this->untappdAPI->getVenueCheckins($vid, $apiKey, $maxID, null, 25);
                $rateLimitRemaining = $response->headers['X-Ratelimit-Remaining'];
                if ($apiKey === false) {
                    $output->writeln(sprintf('[%s] API query limit has been reached, please continue later.', date('H:i:s')));
                    return false;
                } else {
                    $output->writeln(sprintf('[%s] Retrying with key %s', date('H:i:s'), $apiKey));
                }
            }
            $output->writeln(sprintf('[%s] Successfully received venue information', date('H:i:s')));
            $i = 0;
            $j = 0;
            $maxID = $response->body->response->pagination->max_id;
            while ($maxID != "" && $apiKey !== false && !$found) {
                $output->writeln(sprintf('[%s] (%d) Handling %d checkins. Remaining queries: %d.', date('H:i:s'), $i, $response->body->response->checkins->count, $rateLimitRemaining));
                $checkinsData = $response->body->response->checkins->items;
                $this->untappdAPISerializer->handleCheckinsArray($checkinsData);
                if ($input->getOption('update')) {
                    foreach ($checkinsData as $checkin) {
                        if ($highestVenueCheckin && $checkin->checkin_id < $highestVenueCheckin->getId() && !$found) {
                            $found = true;
                            $output->writeln(sprintf('[%s] Checkin %d or lower has been found.', date('H:i:s'), $highestVenueCheckin->getId()));
                        } else {
                            if (!$found) { $j++; }
                        }
                    }
                }
                $venue->setInternalFullHistoryLastMaxId($maxID);
                $this->em->persist($venue);
                $this->em->flush();
                $i++;
                
                $poolKey = $apiKey;
                if (is_null($apiKey)) {
                    $poolKey = 'default';
                }
                $apiKeyPool[$poolKey] = $rateLimitRemaining;
                $apiKey = $this->tools->getBestAPIKey($apiKeyPool);
                
                if ($maxID != "" && $apiKey !== false && !$found) {
                    $output->writeln(sprintf('[%s] (%d) Next page starting at %d.', date('H:i:s'), $i, $maxID));
                    $response = $this->untappdAPI->getVenueCheckins($vid, $apiKey, $maxID, null, 25);
                    $maxID = $response->body->response->pagination->max_id;
                    $rateLimitRemaining = $response->headers['X-Ratelimit-Remaining'];
                }
            }
            $output->writeln(sprintf('[%s] Added %d checkins.', date('H:i:s'), $j));
            if ($maxID == "") {
                $io->note(sprintf('[%s] History is now complete!', date('H:i:s')));
                $venue->setInternalFullHistoryGathered(true);
                $this->em->persist($venue);
                $this->em->flush();
            }
            if ($apiKey === false) {
                $output->writeln(sprintf('[%s] API query limit has been reached, please continue later.', date('H:i:s')));
            }
        } else {
            $output->writeln(sprintf('Couldn\'t get venue information.'));
        }
        
        $output->writeln(sprintf('[%s] Venue %s checkins have been updated', date('H:i:s'), $venue->getName()));
        
        unset($checkinsData);
        unset($highestVenueCheckin);
        unset($venue);
    }
}
