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

class UntappdGetUserFriendlistCommand extends Command
{
    protected static $defaultName = 'untappd:get:user:friendlist';
    private $untappdAPI;
    private $untappdAPISerializer;
    private $em;
    
    protected function configure()
    {
        $this
            ->setDescription('Gets full user friendlist and stores it into the database')
            ->addArgument('username', InputArgument::REQUIRED, 'The username')
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
        $username = $input->getArgument('username');
        $limit = 25;
        
        $user = $this->em->getRepository('\App\Entity\User\User')->findOneBy(array('user_name' => $username));
        if (!$user) {
            throw New \Exception("Couldn't find this user in database");
        }
        
        $accessToken = $user->getInternalUntappdAccessToken();
        
        $offset = $user->getInternalFriendlistLastOffset();
        if(!is_null($offset) && !$input->getOption('force')) {
            $output->writeln(sprintf('Restarting from offset %d', $offset));
        }
        
        if ($input->getOption('force')) {
            $offset = null;
            $output->writeln(sprintf('Force option applied, restarting full run'));
            $user->resetFriends();
            $this->em->persist($user);
            $this->em->flush();
        }
        
        if ($response = $this->untappdAPI->getUserFriends($username, $accessToken, $offset, $limit)) {
            $totalFriends = $response->body->response->found;
            if (count($user->getFriends()) >= $totalFriends && !$input->getOption('force')) {
                throw New \Exception("Friendlist has already been gathered for this user. Use --force to restart.");
            }
            $output->writeln(sprintf('[%s] Successfully received friendlist', date('H:i:s')));
            $i = 0;
            $rateLimitRemaining = $response->headers['X-Ratelimit-Remaining'];
            while ($totalFriends > $offset && $rateLimitRemaining > 0) {
                $rateLimitRemaining = $response->headers['X-Ratelimit-Remaining'];
                $output->writeln(sprintf('[%s] (%d) Handling %d friends. Remaining queries: %d.', date('H:i:s'), $i, $response->body->response->count, $rateLimitRemaining));
                $friendsData = $response->body->response->items;
                $this->untappdAPISerializer->handleFriendsArray($user, $friendsData);
                $offset = $offset + $limit;
                $user->setInternalFriendlistLastOffset($offset);
                $this->em->persist($user);
                $i++;
                if (!is_null($accessToken) && $rateLimitRemaining == 0) {
                    $io->note(sprintf('[%s] Switching to non-authenticated', date('H:i:s')));
                    $accessToken = null;
                }
                if ($totalFriends > $offset && $rateLimitRemaining > 0) {
                    $output->writeln(sprintf('[%s] (%d) Next page starting at offset %d.', date('H:i:s'), $i, $offset));
                    $response = $this->untappdAPI->getUserFriends($username, $accessToken, $offset, $limit);
                }
            }
            $this->em->flush();
            if ($totalFriends < $offset) {
                $io->note(sprintf('[%s] Friendlist is now complete!', date('H:i:s')));
            }
            if ($rateLimitRemaining == 0) {
                $output->writeln(sprintf('[%s] API query limit has been reached, please continue later.', date('H:i:s')));
            }
        } else {
            $output->writeln(sprintf('Couldn\'t get user information.'));
        }
        $io->success('Task done');
    }
}
