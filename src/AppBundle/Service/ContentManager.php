<?php

namespace AppBundle\Service;

use AppBundle\Util\GameUtil;
use Doctrine\ORM\EntityManager;

class ContentManager
{
    private $entityManager;

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function getRulesetList()
    {
        $rulesetRepo = $this->entityManager->getRepository("AppBundle:Ruleset");

        $rulesetEnts = $rulesetRepo->findAllOrderedByPosition();
        $rulesets = [];
        foreach($rulesetEnts as $ruleset)
        {
            $rulesets[] = [
                "id" => $ruleset->getId,
                "title" => $ruleset->getTitle(),
                "body" => $ruleset->getBody()
            ];
        }

        return ["rulesets" => $rulesets];
    }

    public function getTeamMissionList($team)
    {
        $missionRepo = $this->entityManager->getRepository('AppBundle:Mission');

        $missionEnts = $missionRepo->findPostedByTeamOrderedByDate($team);
        $missions = [];
        foreach($missionEnts as $mission)
        {
            $missions[] = [
                "id" => $mission->getId(),
                "title" => $mission->getTitle(),
                "body" => $mission->getBody(),
                "team" => $mission->getTeam == GameUtil::TEAM_HUMAN ? 'human' : 'zombie',
                "post_date" => $mission->getPostDate()
            ];
        }

        return ["missions" => $missions];
    }
}