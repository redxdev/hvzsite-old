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
            ":Game:infection_map.html.twig",
            $list
        );

        return new Response($content);
    }
}