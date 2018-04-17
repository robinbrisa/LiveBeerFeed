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
use App\Service\PushNotification;
use Symfony\Component\Translation\TranslatorInterface;
use App\Entity\PushSubscription;

class LivePushInfoCommand extends Command
{
    protected static $defaultName = 'live:push:info';

    protected function configure()
    {
        $this
            ->setDescription('Pushes info to current events')
        ;
    }
    
    public function __construct(EntityManagerInterface $em, EventStats $stats, TranslatorInterface $translator, PushNotification $pushNotification)
    {
        $this->em = $em;
        $this->em->getConnection()->getConfiguration()->setSQLLogger(null);
        $this->stats = $stats;
        $this->translator = $translator;
        $this->push_notification = $pushNotification;
                
        parent::__construct();
    }
    
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        
        $events = $this->em->getRepository('\App\Entity\Event\Event')->findCurrentEvents();
        if (!$events) {
            $io->error('No events are currently running.');
        } else {
            foreach ($events as $event) {
                $output->writeln(sprintf('[%s] Pushing new info for %s', date('H:i:s'), $event->getName()));
                if (count($event->getVenues()) == 0) {
                    $output->writeln(sprintf('[%s] No venues are related to this event.', date('H:i:s')));
                    continue;
                }
                
                $broadcastMessage = null;
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
                        if (is_null($message->getBroadcastDate())) {
                            $message->setBroadcastDate(new \DateTime());
                            $broadcastMessage = $message;
                        }
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
                $socket = $context->getSocket(\ZMQ::SOCKET_PUSH, 'onNewMessage');
                $socket->connect("tcp://localhost:5555");
                $socket->send(json_encode($data));
                $socket->disconnect("tcp://localhost:5555");
                
                if ($broadcastMessage) {
                    $output->writeln(sprintf('[%s] Sending notification for a new message.', date('H:i:s')));
                    $subscribers = $this->em->getRepository('\App\Entity\PushSubscription')->findBy(array('event' => $event));
                           
                    $message = strip_tags($broadcastMessage->getMessageLine1()) . ' ' . strip_tags($broadcastMessage->getMessageLine2()) . ' ' . strip_tags($broadcastMessage->getMessageLine3());
                    
                    $this->push_notification->pushNotification($subscribers, $event->getName(), $message, $event->getEventLogoNotification());
                    
                    unset($subscribers);
                    unset($broadcastMessage);
                }
            }
            
            
            $output->writeln(sprintf('[%s] Info has been pushed for all current events', date('H:i:s')));
        }
        unset($events);
    }
}
