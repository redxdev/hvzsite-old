<?php

namespace AppBundle\Controller\Web;

use AppBundle\Service\GameStatus;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
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
    public function statusAction()
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
                "game" => array_key_exists("game", $game) ? $game["game"] : null,
                "team" => $teams
            ]
        );

        return new Response($content);
    }

    /**
     * @Route("/players/{page}", name="web_players", requirements={"page" = "\d+"})
     */
    public function playersAction(Request $request, $page = 0)
    {
        $gameStatus = $this->get('game_status');

        $sortBy = $request->get('sort');

        $list = $gameStatus->getPlayerList($page, 10, $sortBy);
        $list["sort"] = $sortBy;
        $list["page"] = $page;

        $content = $this->renderView(
            ":Game:players.html.twig",
            $list
        );

        return new Response($content);
    }

    /**
     * @Route("/players/search", name="web_players_search")
     */
    public function searchPlayersAction(Request $request)
    {
        $gameStatus = $this->get('game_status');

        $term = $request->get('term');
        $list = $gameStatus->searchPlayerList($term);
        $list["sort"] = null;
        $list["search_term"] = $term;

        $content = $this->renderView(
            ":Game:players.html.twig",
            $list
        );

        return new Response($content);
    }

    /**
     * @Route("/infections/{page}", name="web_infections", requirements={"page" = "\d+"})
     */
    public function infectionsAction($page = 0)
    {

        $gameStatus = $this->get('game_status');

        $list = $gameStatus->getInfectionList($page, 10);
        $list["page"] = $page;

        $content = $this->renderView(
            ":Game:infections.html.twig",
            $list
        );

        return new Response($content);
    }
}