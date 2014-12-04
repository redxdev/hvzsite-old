<?php

namespace AppBundle\Controller\Web;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class StatsController extends Controller
{
    /**
     * @Route("/stats/map", name="web_stats_map")
     */
    public function infectionMapAction()
    {
        $gameStatus = $this->get('game_status');

        $list = $gameStatus->getInfectionList(0, PHP_INT_MAX);

        $content = $this->renderView(
            ":Game:map.html.twig",
            $list
        );

        return new Response($content);
    }

    /**
     * @Route("/stats/spread", name="web_stats_spread")
     */
    public function infectionSpreadAction()
    {
        $gameStatus = $this->get('game_status');

        $list = $gameStatus->getInfectionList(0, PHP_INT_MAX);

        $needLookup = [];
        foreach($list["infections"] as $infection)
        {
            $needLookup[$infection["human_id"]] = false;
            if(!array_key_exists($infection["zombie_id"], $needLookup))
                $needLookup[$infection["zombie_id"]] = true;
        }

        $entityManager = $this->getDoctrine()->getManager();
        $userRepo = $entityManager->getRepository("AppBundle:User");

        foreach($needLookup as $id => $lookup)
        {
            if(!$lookup)
                continue;

            $user = $userRepo->findOneById($id);
            if(!$user)
                continue;

            $list["infections"][] = [
                "id" => -1,
                "human" => $user->getFullname(),
                "human_id" => $user->getId(),
                "zombie" => "",
                "zombie_id" => "",
                "time" => new \DateTime("@" . $this->container->getParameter("hvz_game_start")),
                "latitude" => null,
                "longitude" => null
            ];
        }

        $content = $this->renderView(
            ":Game:spread.html.twig",
            $list
        );

        return new Response($content);
    }
}