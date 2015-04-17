<?php
namespace AppBundle\Command;

use AppBundle\Service\ActionLogService;
use AppBundle\External\Notification;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class NotifyCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName("hvz:notify:all")
            ->setDescription("Send a notification to all players using the mobile app")
            ->addArgument(
                'message',
                InputArgument::REQUIRED,
                'the message to send'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $message = $input->getArgument('message');
        $notificationHub = $this->getContainer()->get('notification_hub');

        $notificationHub->broadcastMessage($message);

        $this->getContainer()->get("action_log")->record(
            ActionLogService::TYPE_ADMIN,
            'console',
            'Sent notification',
            true
        );

        $output->writeln("<info>Sent notification:</info>");
        $output->writeln("<info>$message</info>");
    }
}