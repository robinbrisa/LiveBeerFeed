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

class LiveDaemonStartCommand extends EndlessContainerAwareCommand
{
    protected static $defaultName = 'live:daemon:start';
    
    protected function configure()
    {
        $this
            ->setDescription('Start the daemon to send push notifications for lives')
            ->setTimeout(5);
        ;
    }
    
    public function __construct(EntityManagerInterface $em, EventStats $stats,TranslatorInterface $translator)
    {
        //$this->em = $em;
        //$this->em->getConnection()->getConfiguration()->setSQLLogger(null);
        $this->stats = $stats;
        $this->translator = $translator;
        $this->last_checkins_push = new \DateTime();
        
        $this->push_info_command_args = array(
            'command' => 'live:push:info',
            '-e'  => 'prod',
            '--no-debug'  => true,
        );
        
        $this->refresh_events_command_args = array(
            'command' => 'untappd:refresh:events',
            '-e'  => 'prod',
            '--no-debug'  => true,
        );
        
        parent::__construct();
    }
    
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln(sprintf('[%s] ---- Iteration start ----', date('H:i:s')));
        
        $push_info_command = $this->getApplication()->find('live:push:info');
        $refresh_events_command = $this->getApplication()->find('untappd:refresh:events');
        
        $push_info_command->run(new ArrayInput($this->push_info_command_args), $output);
      
        if ($this->last_checkins_push->diff(New \DateTime())->i >= 1) {
            $refresh_events_command->run(new ArrayInput($this->refresh_events_command_args), $output);
            $this->last_checkins_push = new \DateTime();
        }
        
        $output->writeln(sprintf('[%s] ----- Iteration end -----', date('H:i:s')));
    }
}
