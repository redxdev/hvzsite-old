<?php
namespace AppBundle\Command;

use AppBundle\Entity\User;
use AppBundle\Service\ActionLogService;
use AppBundle\Util\GameUtil;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class UserAddCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName("hvz:users:add")
            ->setDescription("Add a user to the database.")
            ->addArgument(
                "name",
                InputArgument::REQUIRED,
                "The full name of the user."
            )
            ->addArgument(
                "email",
                InputArgument::REQUIRED,
                "The email for the user."
            )
            ->addOption(
                "admin",
                null,
                InputOption::VALUE_NONE,
                "If set, the user will be created as an admin."
            )
            ->addOption(
                "mod",
                null,
                InputOption::VALUE_NONE,
                "If set, the user will be created as a moderator."
            )
            ->addOption(
                "zombie",
                null,
                InputOption::VALUE_NONE,
                "If set, the user will be a zombie (the default is human)"
            )
            ->addOption(
                "active",
                null,
                InputOption::VALUE_NONE,
                "If set, the user will be activated upon creation."
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $entityManager = $this->getContainer()->get('doctrine')->getManager();
        $userRepo = $entityManager->getRepository('AppBundle:User');

        $name = $input->getArgument("name");
        $email = $input->getArgument("email");

        if($input->getOption("admin") && $input->getOption("mod")) {
            $output->writeln("<error>Cannot specify both admin and mod options.</error>");
            return;
        }

        $role = "ROLE_USER";
        if($input->getOption("admin"))
            $role = "ROLE_ADMIN";
        else if($input->getOption("mod"))
            $role = "ROLE_MOD";

        $team = $input->getOption("zombie") ? GameUtil::TEAM_ZOMBIE : GameUtil::TEAM_HUMAN;

        if($userRepo->findOneByEmail($email) != null) {
            $output->writeln("<error>There is already a user with that email!</error>");
            return;
        }

        $user = new User();
        $user->setFullname($name);
        $user->setEmail($email);
        $user->setRoles([$role]);
        $user->setTeam($team);
        $user->setActive($input->getOption("active"));

        $actLog = $this->getContainer()->get('action_log');
        $actLog->record(
            ActionLogService::TYPE_ADMIN,
            "console",
            "Created user " . $user->getEmail(),
            false
        );

        $entityManager->persist($user);

        $idGen = $this->getContainer()->get('id_generator');
        $idGen->generateUser($user, false);

        $entityManager->flush();

        $output->writeln("<info>Successfully created user " . $user->getEmail() . "</info>");
    }
}
