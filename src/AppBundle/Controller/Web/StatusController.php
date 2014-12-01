<?php

namespace AppBundle\Controller\Web;

use AppBundle\Service\GameStatus;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class StatusController extends Controller
{
    /**
     * @Route("/", name="status_index")
     */
    public function indexAction()
    {
        $gameStatus = $this->get('game_status');
        $game = $gameStatus->getGameStatus();
        $teams = null;
        if($game["status"] != "no-game")
        {
            $teams = $gameStatus->getTeamStatus();
        }

        $content = $this->renderView(
            ":Game:status.html.twig",
            [
                "game" => $game["game"],
                "team" => $teams
            ]
        );

        return new Response($content);
    }
}