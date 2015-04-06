<?php

namespace AppBundle\Controller\Web;

use AppBundle\Util\GameUtil;
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
        return $this->redirectToRoute("web_status");
    }

    /**
     * @Route("/status", name="web_status")
     */
    public function statusAction()
    {
        $gameStatus = $this->get('game_status');
        $statsManager = $this->get('stats_manager');

        $game = $gameStatus->getGameStatus();
        $teams = null;
        $infectionTimeline = null;
        $infections = null;
        $topPlayers = [];
        if($game["status"] != "no-game")
        {
            $teams = $gameStatus->getTeamStatus();
            $infectionTimeline = $statsManager->getInfectionTimeline()["timeline"];
            $infections = $gameStatus->getInfectionList(0, 5)["infections"];
            $top = $gameStatus->getPlayerList(0, 5, GameUtil::SORT_TEAM)["players"];
            foreach($top as $p)
            {
                if($p['humansTagged'] > 0)
                    $topPlayers[] = $p;
            }
        }

        $content = $this->renderView(
            ":Game:status.html.twig",
            [
                "game" => array_key_exists("game", $game) ? $game["game"] : null,
                "team" => $teams,
                "timeline" => $infectionTimeline,
                "infections" => $infections,
                "top_players" => $topPlayers
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

        $sortBy = $request->query->get('sort');

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

        $term = $request->query->get('term');
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