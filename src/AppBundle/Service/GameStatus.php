<?php

namespace AppBundle\Service;

use AppBundle\Util\GameUtil;
use Doctrine\ORM\EntityManager;

class GameStatus
{
    private $entityManager;
    private $badgeRegistry;
    private $startTime;
    private $endTime;

    public function __construct(EntityManager $entityManager, BadgeRegistry $badgeRegistry, $startTime, $endTime)
    {
        $this->entityManager = $entityManager;
        $this->badgeRegistry = $badgeRegistry;
        $this->startTime = new \DateTime("@" . $startTime);
        $this->endTime = new \DateTime("@" . $endTime);
    }

    public function getGameStart()
    {
        return $this->startTime;
    }

    public function getGameEnd()
    {
        return $this->endTime;
    }

    public function getGameStatus()
    {
        $result = [];
        $now = new \DateTime();
        $toDiff = null;

        if($now > $this->endTime)
        {
            $result["status"] = "no-game";
            return $result;
        }
        else if($now < $this->startTime)
        {
            $result["status"] = "pre-game";
            $toDiff = $this->startTime;
        }
        else
        {
            $result["status"] = "current-game";
            $toDiff = $this->endTime;
        }

        $diff = $now->diff($toDiff);

        $result["game"] = [
            "start" => $this->startTime->getTimestamp(),
            "end" => $this->endTime->getTimestamp(),
            "time" => [
                "days" => $diff->d,
                "hours" => $diff->h,
                "minutes" => $diff->i,
                "seconds" => $diff->s,
                "timestamp" => $toDiff->getTimestamp()
            ]
        ];

        return $result;
    }

    public function getTeamStatus()
    {
        $userRepo = $this->entityManager->getRepository("AppBundle:User");
        return [
            "humans" => $userRepo->findActiveCount(GameUtil::TEAM_HUMAN),
            "zombies" => $userRepo->findActiveCount(GameUtil::TEAM_ZOMBIE)
        ];
    }

    private function buildPlayerList($playerEnts, $protectedInfo = false)
    {
        $players = [];
        foreach($playerEnts as $player)
        {
            $badges = $this->badgeRegistry->getBadges($player);

            $p = [
                'id' => $player->getId(),
                'fullname' => $player->getFullname(),
                'team' => $player->getTeam() == GameUtil::TEAM_HUMAN ? 'human' : 'zombie',
                'humansTagged' => $player->getHumansTagged(),
                'clan' => $player->getClan(),
                'badges' => $badges,
                'avatar' => $player->getWebAvatarPath()
            ];

            if($protectedInfo)
            {
                $p['email'] = $player->getEmail();
                $p['access'] = $player->getRoles()[0];
            }

            $players[] = $p;
        }

        return $players;
    }

    public function getPlayerList($page, $maxPerPage = 10, $sortBy = GameUtil::SORT_TEAM, $allowSortAll = false, $protectedInfo = false)
    {
        $userRepo = $this->entityManager->getRepository("AppBundle:User");

        $playerEnts = [];
        $count = 0;
        switch(strtolower($sortBy))
        {
            default:
            case GameUtil::SORT_TEAM:
                $playerEnts = $userRepo->findActiveOrderedByNumberTaggedAndTeam($page, $maxPerPage);
                $count = $userRepo->findActiveNormalCount();
                break;

            case GameUtil::SORT_CLAN:
                $playerEnts = $userRepo->findActiveOrderedByClan($page, $maxPerPage);
                $count = $userRepo->findActiveWithClanCount();
                break;

            case GameUtil::SORT_MODS:
                $playerEnts = $userRepo->findActiveMods($page, $maxPerPage);
                $count = $userRepo->findActiveModsCount();
                break;

            case GameUtil::SORT_ALL:
                if(!$allowSortAll) {
                    return ['continues' => false, 'players' => []];
                }

                $playerEnts = $userRepo->findAllByPage($page, $maxPerPage);
                $count = $userRepo->findCount();
                break;
        }

        $players = $this->buildPlayerList($playerEnts, $protectedInfo);

        return [
            'continues' => $page < ($count / $maxPerPage - 1),
            'players' => $players
        ];
    }

    public function searchPlayerList($term, $onlyActive = true, $protectedInfo = false)
    {
        if($term === null || empty($term))
            return ['continues' => false, 'players' => []];

        $userRepo = $this->entityManager->getRepository("AppBundle:User");

        $playerEnts = $userRepo->findInSearchableFields($term, $onlyActive, $protectedInfo);
        $players = $this->buildPlayerList($playerEnts, $protectedInfo);

        return [
            'continues' => false,
            'players' => $players
        ];
    }

    public function getInfectionList($page, $maxPerPage = 10)
    {
        $infectionRepo = $this->entityManager->getRepository("AppBundle:InfectionSpread");

        $infectionEnts = $infectionRepo->findPageOrderedByTime($page, $maxPerPage);
        $infections = [];
        foreach($infectionEnts as $infection)
        {
            $infections[] = [
                "id" => $infection->getId(),
                "human" => $infection->getHuman()->getFullname(),
                "human_id" => $infection->getHuman()->getId(),
                "zombie" => $infection->getZombie()->getFullname(),
                "zombie_id" => $infection->getZombie()->getId(),
                "time" => $infection->getTime(),
                "latitude" => $infection->getLatitude(),
                "longitude" => $infection->getLongitude()
            ];
        }

        $count = $infectionRepo->findCount();

        return [
            "continues" => $page < ($count / $maxPerPage - 1),
            "infections" => $infections
        ];
    }
}