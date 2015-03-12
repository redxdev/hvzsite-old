<?php

namespace AppBundle\Service;

use AppBundle\Entity\User;
use AppBundle\Entity\Clan;
use AppBundle\Util\GameUtil;
use Doctrine\ORM\EntityManager;

class ProfileManager
{
    private $entityManager;
    private $badgeReg;
    private $idGenerator;

    public function __construct(EntityManager $entityManager, BadgeRegistry $badgeReg, IdGenerator $idGenerator)
    {
        $this->entityManager = $entityManager;
        $this->badgeReg = $badgeReg;
        $this->idGenerator = $idGenerator;
    }

    public function getClanInfo(Clan $clan, $user = null, $protectedInfo = false)
    {
        if($clan == null)
            return null;

        $info = [
            "name" => $clan->getName(),
            "owner" => $clan->getOwner()->getFullname()
        ];

        if($protectedInfo && $user != null && $user == $clan->getOwner())
            $info["code"] = $clan->getCode();

        return $info;
    }

    public function getProfileInfo(User $user, $protectedInfo = false)
    {
        $profile = [
            "id" => $user->getId(),
            "apikey" => $user->getApiKey(),
            "fullname" => $user->getFullname(),
            "email" => $user->getEmail(),
            "clan" => $this->getClanInfo($user->getClan(), $user, $protectedInfo),
            "team" => $user->getTeam() == GameUtil::TEAM_HUMAN ? 'human' : 'zombie',
            "zombieId" => $user->getZombieId(),
            "humansTagged" => $user->getHumansTagged(),
            "badges" => $this->badgeReg->getBadges($user),
            "avatar" => $user->getWebAvatarPath(),
            "humanIds" => [],
            "infections" => [],
            "qr_data" => json_encode(array(
                "human" => count($user->getHumanIds()) > 0 ?
                    $user->gethumanIds()[0]->getIdString() : "invalid",
                "zombie" => $user->getZombieId()
            ), JSON_FORCE_OBJECT)
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
        $infections = $infectionRepo->findByZombieOrderedByTime($user);
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
            $profiles[] = $profile;
        }

        return ["profiles" => $profiles];
    }

    public function createClan(User $owner, $flush = true)
    {
        if($owner->getClan() != null)
            return [
                "status" => "error",
                "errors" => [
                    "Player is already in a clan."
                ]
            ];

        $clan = new Clan();
        $clan->setCode($this->idGenerator->generate());
        $clan->setOwner($owner);
        $clan->addMember($owner);
        $owner->setClan($clan);

        $this->entityManager->persist($clan);

        if($flush) {
            $this->entityManager->flush();
        }
    }
}