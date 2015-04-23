<?php

namespace AppBundle\Service;

use AppBundle\Entity\InfectionSpread;
use AppBundle\Entity\User;
use AppBundle\Util\GameUtil;
use Doctrine\ORM\EntityManager;

class GameManager
{
    private $actLog;
    private $entityManager;
    private $badgeReg;
    private $gameStatus;

    public function __construct(ActionLogService $actLog, EntityManager $entityManager, BadgeRegistry $badgeReg, GameStatus $gameStatus)
    {
        $this->actLog = $actLog;
        $this->entityManager = $entityManager;
        $this->badgeReg = $badgeReg;
        $this->gameStatus = $gameStatus;
    }

    public function processInfection($humanIdStr, $zombieIdStr, $latitude = null, $longitude = null)
    {
        $errors = [];

        $idRepo = $this->entityManager->getRepository("AppBundle:HumanId");
        $userRepo = $this->entityManager->getRepository("AppBundle:User");

        $humanId = $idRepo->findOneByIdString(trim(strtolower($humanIdStr)));
        $zombie = $userRepo->findOneByZombieId(trim(strtolower($zombieIdStr)));

        if($this->gameStatus->getGameEnd() < new \DateTime())
        {
            $errors[] = "You can't register infections after the game has ended!";
        }

        if(!$humanId || !$humanId->getActive() || !$humanId->getUser()->getActive())
        {
            $errors[] = "Unknown human id";
        }

        if(!$zombie || !$zombie->getActive())
        {
            $errors[] = "Unknown zombie id";
        }

        if(count($errors) == 0)
        {
            if($humanId->getUser()->getTeam() != GameUtil::TEAM_HUMAN)
            {
                $errors[] = "Victim is not a human";
            }

            if($zombie->getTeam() != GameUtil::TEAM_ZOMBIE)
            {
                $errors[] = "Attacker is not a zombie";
            }
        }

        if(count($errors) != 0)
        {
            return [
                "status" => "error",
                "errors" => $errors,
                "human" => $humanIdStr,
                "zombie" => $zombieIdStr
            ];
        }
        else
        {
            $this->actLog->record(
                ActionLogService::TYPE_GAME,
                $zombie->getEmail(),
                'zombified player ' . $humanId->getUser()->getEmail(),
                false
            );

            $this->actLog->record(
                ActionLogService::TYPE_GAME,
                $humanId->getUser()->getEmail(),
                'zombified by player ' . $zombie->getEmail(),
                false
            );

            $infection = new InfectionSpread();
            $infection->setHuman($humanId->getUser());
            $infection->setZombie($zombie);
            $infection->setLatitude($latitude == 0 ? null : $latitude);
            $infection->setLongitude($longitude == 0 ? null : $longitude);
            $this->entityManager->persist($infection);

            $humanId->getUser()->setTeam(GameUtil::TEAM_ZOMBIE);
            $humanId->setActive(false);

            $zombie->setHumansTagged($zombie->getHumansTagged() + 1);

            $this->applyBadges($humanId->getUser(), $zombie, $infection);

            $this->entityManager->flush();

            return [
                "status" => "ok",
                "human_name" => $humanId->getUser()->getFullname(),
                "human_id" => $humanId->getUser()->getId(),
                "zombie_name" => $zombie->getFullname(),
                "zombie_id" => $zombie->getId()
            ];
        }
    }

    private function applyBadges($human, $zombie, $infection)
    {
        $infectionRepo = $this->entityManager->getRepository("AppBundle:InfectionSpread");

        $this->badgeReg->addBadge($human, 'infected', false);

        $now = new \DateTime();

        $hour = intval($now->format('G'));
        $day = intval($now->format('w'));

        if($hour >= 6 && $hour < 8)
        {
            $this->badgeReg->addBadge($zombie, 'early-bird', false);
        }
        else if($hour >= 23)
        {
            $this->badgeReg->addBadge($human, 'mission-aint-over', false);
        }

        if($day == 0)
        {
            $this->badgeReg->addBadge($human, 'bad-start', false);
        }
        else if($day >= 4)
        {
            $this->badgeReg->addBadge($human, 'so-close', false);
        }

        if($infectionRepo->findIsRecentDeath($zombie) === true)
        {
            $this->badgeReg->addBadge($zombie, 'quick-turnaround', false);
        }

        $recentKills = $infectionRepo->findForKillstreak($zombie);
        $recentKills[] = $infection;

        $this->applyKillstreak($recentKills, 2, 'streak-2', $zombie);
        $this->applyKillstreak($recentKills, 3, 'streak-3', $zombie);
        $this->applyKillstreak($recentKills, 4, 'streak-4', $zombie);
        $this->applyKillstreak($recentKills, 5, 'streak-5', $zombie);
        $this->applyKillstreak($recentKills, 6, 'streak-6', $zombie);
        $this->applyKillstreak($recentKills, 7, 'streak-7', $zombie);
        $this->applyKillstreak($recentKills, 8, 'streak-8', $zombie);
        $this->applyKillstreak($recentKills, 9, 'streak-9', $zombie);
        $this->applyKillstreak($recentKills, 10, 'streak-10', $zombie);
    }

    private function applyKillstreak($recent, $streak, $badge, $zombie)
    {
        $available = array();
        foreach($recent as $infection)
        {
            if(!array_key_exists($streak, $infection->getKillstreaks()))
                $available[] = $infection;
        }

        if(count($available) >= $streak)
        {
            $this->badgeReg->addBadge($zombie, $badge, false);

            foreach($available as $infection)
            {
                $streaks = $infection->getKillstreaks();
                for($i = $streak; $i > 1; $i--)
                {
                    $streaks[$i] = true;
                }

                $infection->setKillstreaks($streaks);
            }
        }
    }

    public function isValidAntiVirusTime()
    {
        $now = new \DateTime();
        $hour = intval($now->format('G'));
        $day = intval($now->format('w'));

        if($hour >= 17 && $hour < 23)
        {
            return false;
        }

        if($day > 5 && $hour >= 2)
        {
            return false;
        }

        return true;
    }

    public function processAntiVirus($avIdStr, $zombieIdStr)
    {
        if(!$this->isValidAntiVirusTime())
        {
            return [
                "status" => "error",
                "errors" => ["You may not use an antivirus at this time"],
                "antivirus" => $avIdStr,
                "zombie" => $zombieIdStr
            ];
        }

        $errors = [];

        $avRepo = $this->entityManager->getRepository("AppBundle:AntiVirusId");
        $userRepo = $this->entityManager->getRepository("AppBundle:User");

        $zombie = $userRepo->findOneByZombieId(trim(strtolower($zombieIdStr)));

        if(!$zombie || !$zombie->getActive())
        {
            return [
                "status" => "error",
                "errors" => ["Unknown zombie id"],
                "antivirus" => $avIdStr,
                "zombie" => $zombieIdStr
            ];
        }

        if($zombie->getTeam() != GameUtil::TEAM_ZOMBIE)
        {
            return [
                "status" => "error",
                "errors" => ["Player is not a zombie"],
                "antivirus" => $avIdStr,
                "zombie" => $zombieIdStr
            ];
        }

        if($avRepo->findHasUsedAntivirus($zombie))
        {
            return [
                "status" => "error",
                "errors" => ["Zombie has already used an antivirus!"],
                "antivirus" => $avIdStr,
                "zombie" => $zombieIdStr
            ];
        }

        $avId = $avRepo->findOneByIdString(trim(strtolower($avIdStr)));

        if(!$avId)
        {
            $errors[] = "Unknown antivirus code";
        }
        else if(!$avId->getActive())
        {
            $errors[] = "Antivirus has already been used";
        }

        if(count($errors) != 0)
        {
            return [
                "status" => "error",
                "errors" => $errors,
                "antivirus" => $avIdStr,
                "zombie" => $zombieIdStr
            ];
        }
        else
        {
            $this->actLog->record(
                ActionLogService::TYPE_GAME,
                $zombie->getEmail(),
                'Used an antivirus - ' . $avId->getIdString(),
                false
            );

            $avId->setActive(false);
            $avId->setUser($zombie);
            $zombie->setTeam(GameUtil::TEAM_HUMAN);

            $this->badgeReg->addBadge($zombie, 'used-av', false);

            $this->entityManager->flush();

            return [
                "status" => "ok",
                "zombie_name" => $zombie->getFullname(),
                "zombie_id" => $zombie->getId()
            ];
        }
    }
}