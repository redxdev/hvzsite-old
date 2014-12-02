<?php

namespace AppBundle\Service;

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
                "title" => $ruleset->getTitle(),
                "body" => $ruleset->getBody()
            ];
        }

        return ["rulesets" => $rulesets];
    }
}