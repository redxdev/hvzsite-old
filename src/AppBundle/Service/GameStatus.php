<?php

namespace AppBundle\Service;

use Doctrine\ORM\EntityManager;

use AppBundle\Entity\User;

class GameStatus
{
    private $entityManager;
    private $startTime;
    private $endTime;

    public function __construct(EntityManager $entityManager, $startTime, $endTime)
    {
        $this->entityManager = $entityManager;
        $this->startTime = new \DateTime($startTime);
        $this->endTime = new \DateTime($endTime);
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
            "diff" => $diff,
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
            "humans" => $userRepo->findActiveCount(User::TEAM_HUMAN),
            "zombies" => $userRepo->findActiveCount(User::TEAM_ZOMBIE)
        ];
    }
}