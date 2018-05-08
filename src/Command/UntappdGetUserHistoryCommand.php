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

class UntappdGetUserHistoryCommand extends Command
{
    protected static $defaultName = 'untappd:get:user:history';
    private $untappdAPI;
    private $untappdAPISerializer;
    private $em;
    
    protected function configure()
    {
        $this
            ->setDescription('Gets full user history and stores it into the database')
            ->addArgument('username', InputArgument::REQUIRED, 'The username')
            ->addOption('update', 'u', InputOption::VALUE_NONE, 'Updates checkins to the latest synchronized')
            ->addOption('force', 'f', InputOption::VALUE_NONE, 'Force restart of the whole data collection');
        ;
    }

    public function __construct(UntappdAPI $untappdAPI, UntappdAPISerializer $untappdAPISerializer, EntityManagerInterface $em)
    {
        $this->em = $em;
        $this->em->getConnection()->getConfiguration()->setSQLLogger(null);
        $this->untappdAPI = $untappdAPI;
        $this->untappdAPISerializer = $untappdAPISerializer;
        
        parent::__construct();
    }
    
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $username = $input->getArgument('username');
        
        $user = $this->em->getRepository('\App\Entity\User\User')->findOneBy(array('user_name' => $username));
        if (!$user) {
            throw New \Exception("Couldn't find this user in database");
        }
        $userID = $user->getId();
        
        if (is_null($user->getInternalUntappdAccessToken())) {
            throw New \Exception("Access token for this user is unknown");
        }
        
        if ($user->getInternalFullHistoryGathered() && !$input->getOption('force') && !$input->getOption('update')) {
            throw New \Exception("History has already been gathered for this user. Use --force to restart.");
        }
        
        $maxID = $user->getInternalFullHistoryLastMaxId();
        if(!is_null($maxID) && !$input->getOption('force') && !$input->getOption('update')) {
            $output->writeln(sprintf('[%s] Restarting from checkin %d', date('H:i:s'), $maxID));
        }
        
        if ($highestUserCheckin = $this->em->getRepository('App\Entity\Checkin\Checkin')->getUserCheckins($user, null, 1)) {
            $highestUserCheckin = $highestUserCheckin[0];
        }
        $found = false;
        
        if ($highestUserCheckin && $input->getOption('update')) {
            $maxID = null;
            $output->writeln(sprintf('[%s] Update option applied, updating until checkin %d', date('H:i:s'), $highestUserCheckin->getId()));
        }
        
        if ($input->getOption('force')) {
            $maxID = null;
            $output->writeln(sprintf('Force option applied, restarting full history'));
            $user->setInternalFullHistoryGathered(false);
        }
        
        if ($response = $this->untappdAPI->getUserActivityFeed(null, $user->getInternalUntappdAccessToken(), $maxID, null, 50)) {
            $output->writeln(sprintf('[%s] Successfully received user information', date('H:i:s')));
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
                        if ($highestUserCheckin && $checkin->checkin_id == $highestUserCheckin->getId()) {
                            $found = true;
                            $output->writeln(sprintf('[%s] Checkin %d has been found.', date('H:i:s'), $highestUserCheckin->getId()));
                        } else {
                            if (!$found) { $j++; }
                        }
                    }
                }
                $user->setInternalFullHistoryLastMaxId($maxID);
                $user->setInternalLatestCheckinRefresh(new \DateTime());
                $this->em->persist($user);
                $this->em->flush();
                $i++;
                unset($response);
                unset($checkinsData);
                unset($user);
                $this->em->clear();
                $user = $this->em->getRepository('\App\Entity\User\User')->find($userID);
                if ($maxID != "" && $rateLimitRemaining > 0 && !$found) {
                    $output->writeln(sprintf('[%s] (%d) Next page starting at %d.', date('H:i:s'), $i, $maxID));
                    $response = $this->untappdAPI->getUserActivityFeed(null, $user->getInternalUntappdAccessToken(), $maxID, null, 50);
                    $maxID = $response->body->response->pagination->max_id;
                    $rateLimitRemaining = $response->headers['X-Ratelimit-Remaining'];
                }
                if ($maxID == "") {
                    $io->note(sprintf('[%s] History is now complete!', date('H:i:s')));
                    $user->setInternalFullHistoryGathered(true);
                    $this->em->persist($user);
                    $this->em->flush();
                }
            }
            // Empty account
            if (isset($response) && $response->body->response->checkins->count == 0) {
                $user->setInternalLatestCheckinRefresh(new \DateTime());
                $user->setInternalFullHistoryGathered(true);
                $this->em->persist($user);
                $this->em->flush();
            }
            if ($rateLimitRemaining == 0) {
                $output->writeln(sprintf('[%s] API query limit has been reached, please continue later.', date('H:i:s')));
            }
        } else {
            $output->writeln(sprintf('Couldn\'t get user information.'));
        }
        $io->success('Task done');
        gc_collect_cycles();
    }
}
