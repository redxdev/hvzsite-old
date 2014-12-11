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

    public function getProfileInfo(User $user, $protectedInfo = false)
    {
        $profile = [
            "id" => $user->getId(),
            "apikey" => $user->getApiKey(),
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

        if($protectedInfo)
        {
            $profile["access"] = $user->getRoles()[0];
            $profile["apiFailures"] = $user->getApiFails();
            $profile["maxApiFailures"] = $user->getMaxApiFails();
        }

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
                "id" => $infection->getId(),
                "human" => $infection->getHuman()->getFullname(),
                "human_id" => $infection->getHuman()->getId(),
                "zombie" => $infection->getZombie()->getFullname(),
                "zombie_id" => $infection->getZombie()->getId(),
                "time" => $infection->getTime()->getTimestamp(),
                "longitude" => $infection->getLongitude(),
                "latitude" => $infection->getLatitude()
            ];
        }

        return ["profile" => $profile];
    }

    public function getUnprintedProfiles()
    {
        $userRepo = $this->entityManager->getRepository("AppBundle:User");
        $profileEnts = $userRepo->findActiveUnprinted();
        $profiles = [];
        foreach($profileEnts as $profile)
        {
            $profile = $this->getProfileInfo($profile);
            $profile["profile"]["qr_data"] = json_encode(array(
                "human" => count($profile["profile"]["humanIds"]) > 0 ?
                    $profile["profile"]["humanIds"][0]["id_string"] : "invalid",
                "zombie" => $profile["profile"]["zombieId"]
            ), JSON_FORCE_OBJECT);
            $profiles[] = $profile;
        }

        return ["profiles" => $profiles];
    }
}