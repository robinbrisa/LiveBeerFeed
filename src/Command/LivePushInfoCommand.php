<?php

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Doctrine\ORM\EntityManagerInterface;
use App\Service\EventStats;
use Symfony\Component\Translation\TranslatorInterface;

class LivePushInfoCommand extends Command
{
    protected static $defaultName = 'live:push:info';

    protected function configure()
    {
        $this
            ->setDescription('Pushes info to current events')
        ;
    }
    
    public function __construct(EntityManagerInterface $em, EventStats $stats, TranslatorInterface $translator)
    {
        $this->em = $em;
        $this->stats = $stats;
        $this->translator = $translator;
        
        parent::__construct();
    }
    
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        
        $events = $this->em->getRepository('\App\Entity\Event\Event')->findCurrentEvents();
        if (!$events) {
            $io->success('No events are currently running.');
            return true;
        }
        
        foreach ($events as $event) {
            $output->writeln(sprintf('Sending new info for %s', $event->getName()));
            
            $this->translator->setLocale($event->getLocale());
            
            $data = array();
            $data['line1'] = '<span class="info-major">' . $this->translator->trans('live.welcome') . '</span>';
            $data['line2'] = '<span class="info-major">' . $event->getName() . '</span>';
            $data['line3'] = $event->getStartDate()->format('d/m/Y') . ' - ' . $event->getEndDate()->format('d/m/Y');
            if ($event->getLastInfoStats()) {
                $event->setLastInfoStats(0);
                if ($message = $this->em->getRepository('\App\Entity\Event\Message')->findInfoMessageToDisplay($event)) {
                    $data['line1'] = $message->getMessageLine1();
                    $data['line2'] = $message->getMessageLine2();
                    $data['line3'] = $message->getMessageLine3();
                    $message->setLastTimeDisplayed(new \DateTime());
                }
            } else {
                if ($statistics = $this->stats->returnRandomStatistic($event)) {
                    $data = $statistics;
                }
                $event->setLastInfoStats(1);
            }
            
            $this->em->persist($event);
            $this->em->flush();
            
            $data['push_type'] = 'info';
            $data['push_topic'] = 'info-event-'.$event->getId();
            
            $context = new \ZMQContext();
            $socket = $context->getSocket(\ZMQ::SOCKET_PUSH, 'onNewInfo');
            $socket->connect("tcp://localhost:5555");
            $socket->send(json_encode($data));
        }
        
        $io->success('Info has been sent for all current events');
    }
}
