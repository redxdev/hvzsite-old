<?php

namespace AppBundle\Controller\Web;

use AppBundle\Service\GameStatus;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class StatusController extends Controller
{
    /**
     * @Route("/", name="web_index")
     */
    public function indexAction()
    {
        return $this->redirect($this->generateUrl("web_status"));
    }

    /**
     * @Route("/status", name="web_status")
     */
    public function statusction()
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