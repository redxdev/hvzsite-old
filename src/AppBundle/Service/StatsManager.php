<?php

namespace AppBundle\Service;

use Doctrine\ORM\EntityManager;

class StatsManager
{
    private $entityManager;

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function getInfectionTimeline()
    {
        $infectionRepo = $this->entityManager->getRepository('AppBundle:InfectionSpread');
        $data = $infectionRepo->findCountGroupedByTime();

        $timeline = [];
        foreach($data as $entry)
        {
            $time = $entry["time"];
            $timeline[] = [
                "count" => $entry["totalCount"],
                "time" => $time,
                "time_str" => $time->format('D H:00')
            ];
        }

        return ["timeline" => $timeline];
    }
}