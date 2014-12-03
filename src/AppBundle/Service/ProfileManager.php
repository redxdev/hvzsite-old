<?php

namespace AppBundle\Service;

use AppBundle\Entity\User;
use AppBundle\Util\GameUtil;
use Doctrine\ORM\EntityManager;

class ProfileManager
{
    private $entityManager;
    private $badgeReg;

    public function __construct(EntityManager $entityManager, BadgeRegistry $badgeReg)
    {
        $this->entityManager = $entityManager;
        $this->badgeReg = $badgeReg;
    }

    public function getProfileInfo(User $user)
    {
        $profile = [
            "fullname" => $user->getFullname(),
            "email" => $user->getEmail(),
            "clan" => $user->getClan(),
            "team" => $user->getTeam() == GameUtil::TEAM_HUMAN ? 'human' : 'zombie',
            "zombieId" => $user->getZombieId(),
            "humansTagged" => $user->getHumansTagged(),
            "badges" => $this->badgeReg->getBadges($user),
            "avatar" => $user->getWebAvatarPath(),
            "humanIds" => [],
            "infections" => []
        ];

        $humanIds = $user->getHumanIds();
        foreach($humanIds as $humanId)
        {
            $profile["humanIds"][] = [
                "id_string" => $humanId->getIdString(),
                "active" => $humanId->getActive()
            ];
        }

        $infectionRepo = $this->entityManager->getRepository("AppBundle:InfectionSpread");
        $infections = $infectionRepo->findByZombie($user);
        foreach($infections as $infection)
        {
            $profile["infections"][] = [
                "human" => $infection->getHuman(),
                "zombie" => $infection->getZombie(),
                "time" => $infection->getTime(),
                "longitude" => $infection->getLongitude(),
                "latitude" => $infection->getLatitude()
            ];
        }

        return ["profile" => $profile];
    }
}