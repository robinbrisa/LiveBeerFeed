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
        
        $maxID = $venue->getInternalFullHistoryLastMaxId();
        if(!is_null($maxID) && !$input->getOption('force') && !$input->getOption('update')) {
            $output->writeln(sprintf('Restarting from checkin %d', $maxID));
        }
        if ($highestVenueCheckin && $input->getOption('update')) {
            $maxID = null;
            $output->writeln(sprintf('Update option applied, updating until checkin ' . $highestVenueCheckin->getId()));
        }
        if ($input->getOption('force')) {
            $maxID = null;
            $output->writeln(sprintf('Force option applied, restarting full history'));
            $venue->setInternalFullHistoryGathered(false);
        }
        if ($response = $this->untappdAPI->getVenueCheckins($vid, null, $maxID, null, 25)) {
            $output->writeln(sprintf('[%s] Successfully received venue information', date('H:i:s')));
            $i = 0;
            $j = 0;
            $maxID = $response->body->response->pagination->max_id;
            $rateLimitRemaining = $response->headers['X-Ratelimit-Remaining'];
            while ($maxID != "" && $rateLimitRemaining > 0 && !$found) {
                $output->writeln(sprintf('[%s] (%d) Handling %d checkins. Remaining queries: %d.', date('H:i:s'), $i, $response->body->response->checkins->count, $rateLimitRemaining));
                $checkinsData = $response->body->response->checkins->items;
                $this->untappdAPISerializer->handleCheckinsArray($checkinsData);
                if ($input->getOption('update')) {
                    foreach ($checkinsData as $checkin) {
                        if ($highestVenueCheckin && $checkin->checkin_id == $highestVenueCheckin->getId()) {
                            $found = true;
                            $output->writeln(sprintf('[%s] Checkin %d has been found.', date('H:i:s'), $highestVenueCheckin->getId()));
                        } else {
                            if (!$found) { $j++; }
                        }
                    }
                }
                $venue->setInternalFullHistoryLastMaxId($maxID);
                $this->em->persist($venue);
                $this->em->flush();
                $i++;
                if ($maxID != "" && $rateLimitRemaining > 0 && !$found) {
                    $output->writeln(sprintf('[%s] (%d) Next page starting at %d.', date('H:i:s'), $i, $maxID));
                    $response = $this->untappdAPI->getVenueCheckins($vid, null, $maxID, null, 25);
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
            if ($rateLimitRemaining == 0) {
                $output->writeln(sprintf('[%s] API query limit has been reached, please continue later.', date('H:i:s')));
            }
        } else {
            $output->writeln(sprintf('Couldn\'t get venue information.'));
        }
        $io->success('Task done');
    }
}
