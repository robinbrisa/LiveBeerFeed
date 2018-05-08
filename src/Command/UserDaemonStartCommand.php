<?php

namespace App\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Wrep\Daemonizable\Command\EndlessContainerAwareCommand;
use Symfony\Component\Console\Input\ArrayInput;
use App\Service\EventStats;
use Symfony\Component\Translation\TranslatorInterface;
use Doctrine\ORM\EntityManagerInterface;
use App\Service\Tools;

class UserDaemonStartCommand extends EndlessContainerAwareCommand
{
    protected static $defaultName = 'user:daemon:start';
    private $tools;
    private $untappdAPISerializer;
    private $em;
    
    protected function configure()
    {
        $this
            ->setDescription('Start the daemon to refresh user checkins')
            ->setTimeout(3);
        ;
    }
    
    public function __construct(EventStats $stats,TranslatorInterface $translator, EntityManagerInterface $em, Tools $tools)
    {
        $this->em = $em;
        $this->em->getConnection()->getConfiguration()->setSQLLogger(null);
        $this->tools = $tools;
        
        $this->get_user_history_command_args = array(
            'command' => 'untappd:get:user:history',
            '-e'  => 'prod',
            '--no-debug'  => true,
        );
        
        $this->get_update_user_command_args = array(
            'command' => 'untappd:get:user:history',
            '--update' => true,
            '-e'  => 'prod',
            '--no-debug'  => true,
        );
        
        parent::__construct();
    }
    
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln(sprintf('[%s] ---- Iteration start ----', date('H:i:s')));
        
        $usersToGather = $this->em->getRepository('\App\Entity\User\User')->getUsersWaitingForFullHistory();
        $apiKeys = $this->tools->getAPIKeysPool();
        
        $get_user_history_command = $this->getApplication()->find('untappd:get:user:history');
        
        foreach ($usersToGather as $user) {
            foreach ($apiKeys as $apiKey => $keyQueries) {
                if ($user->getInternalUntappdAccessToken() == $apiKey && $keyQueries > 40) {
                    $output->writeln(sprintf('[%s] Getting history for user %s.', date('H:i:s'), $user->getUsername() ));
                    $this->get_user_history_command_args['username'] = $user->getUsername();
                    $get_user_history_command->run(new ArrayInput($this->get_user_history_command_args), $output);
                }
            }
        }
        
        $usersToUpdate = $this->em->getRepository('\App\Entity\User\User')->getUsersToRefresh();
        
        if ($usersToUpdate) {
            foreach ($usersToUpdate as $user) {
                foreach ($apiKeys as $apiKey => $keyQueries) {
                    if ($user->getInternalUntappdAccessToken() == $apiKey && $keyQueries > 10) {
                        $output->writeln(sprintf('[%s] Refreshing user %s.', date('H:i:s'), $user->getUsername()));
                        $this->get_update_user_command_args['username'] = $user->getUsername();
                        $get_user_history_command->run(new ArrayInput($this->get_update_user_command_args), $output);
                        
                        $attending = $this->em->getRepository('\App\Entity\Event\Event')->getFutureOrCurrentEventsUserIsAttending($user);
                        foreach ($attending as $event) {
                            $pushData = array(
                                'push_type' => 'checked_in_beers', 
                                'push_topic' => 'taplist-'.$event->getId().'-'.$user->getId(),
                                'list' => $this->tools->getEventBeersUserHasCheckedIn($user, $event)
                            );
                            $context = new \ZMQContext();
                            $socket = $context->getSocket(\ZMQ::SOCKET_PUSH, 'onNewMessage');
                            $socket->connect("tcp://localhost:5555");
                            $socket->send(json_encode($pushData));
                            $socket->disconnect("tcp://localhost:5555");
                        }
                    }
                }
            }
        } else {
            $output->writeln(sprintf('[%s] No user to refresh.', date('H:i:s')));
        }
        
        $output->writeln(sprintf('[%s] ----- Iteration end -----', date('H:i:s')));
    }
}
