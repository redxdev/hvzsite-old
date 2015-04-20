<?php
namespace AppBundle\Command;

use AppBundle\Service\ActionLogService;
use AppBundle\Util\GameUtil;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class InfectCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName("hvz:infect:oz")
            ->setDescription("Infect players as OZs by their ids")
            ->addArgument(
                'ids',
                InputArgument::IS_ARRAY,
                'ids of players'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $ids = $input->getArgument('ids');
        $users = [];
        $entityManager = $this->getContainer()->get('doctrine')->getManager();
        $userRepo = $entityManager->getRepository('AppBundle:User');
        foreach($ids as $id)
        {
            $user = $userRepo->findOneById($id);
            if($user == null)
            {
                $output->writeln("<error>Unknown user id $id</error>");
                return;
            }

            if($user->getTeam() == GameUtil::TEAM_ZOMBIE)
            {
                $output->writeln("<error>User " . $user->getFullname() . " ($id) is already a zombie</error>");
                return;
            }

            $users[] = $user;
        }

        $this->getContainer()->get("action_log")->record(
            ActionLogService::TYPE_ADMIN,
            'console',
            'OZ\'d players',
            false
        );

        $badgeReg = $this->getContainer()->get('badge_registry');
        foreach($users as $user)
        {
            $user->setTeam(GameUtil::TEAM_ZOMBIE);
            $badgeReg->addBadge($user, 'oz', false);
            $output->writeln("<info>Infected " . $user->getFullname() . "</info>");
        }

        $entityManager->flush();
        $output->writeln("<info>Flushed to database</info>");
    }
}
