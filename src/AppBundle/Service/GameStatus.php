<?php

namespace AppBundle\Service;

use AppBundle\Util\GameUtil;
use Doctrine\ORM\EntityManager;

use AppBundle\Entity\User;

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

    private function buildPlayerList($playerEnts)
    {
        $players = [];
        foreach($playerEnts as $player)
        {
            $badges = $this->badgeRegistry->getBadges($player);

            $players[] = [
                'id' => $player->getId(),
                'fullname' => $player->getFullname(),
                'team' => $player->getTeam() == GameUtil::TEAM_HUMAN ? 'human' : 'zombie',
                'tags' => $player->getHumansTagged(),
                'clan' => $player->getClan(),
                'badges' => $badges,
                'avatar' => $player->getWebAvatarPath()
            ];
        }

        return $players;
    }

    public function getPlayerList($page, $maxPerPage = 10, $sortBy = GameUtil::SORT_TEAM)
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
        }

        $players = $this->buildPlayerList($playerEnts);

        return [
            'continues' => $page < ($count / $maxPerPage - 1),
            'players' => $players
        ];
    }

    public function searchPlayerList($term)
    {
        if($term == null || empty($term))
            return ['continues' => false, 'players' => []];

        $userRepo = $this->entityManager->getRepository("AppBundle:User");

        $playerEnts = $userRepo->findInSearchableFields($term);
        $players = $this->buildPlayerList($playerEnts);

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
                "zombie" => $infection->getZombie()->getFullname(),
                "time" => $infection->getTime()
            ];
        }

        $count = $infectionRepo->findCount();

        return [
            "continues" => $page < ($count / $maxPerPage - 1),
            "infections" => $infections
        ];
    }
}