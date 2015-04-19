<?php
namespace AppBundle\Command;

use AppBundle\Service\ActionLogService;
use AppBundle\Util\GameUtil;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class UserCountCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName("hvz:users:count")
            ->setDescription("Get a count of normal players");
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $entityManager = $this->getContainer()->get('doctrine')->getManager();
        $userRepo = $entityManager->getRepository('AppBundle:User');
        $output->writeln("<info>".$userRepo->findActiveNormalUsersCount()."</info>");
    }
}
