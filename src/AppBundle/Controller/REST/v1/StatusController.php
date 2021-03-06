<?php

namespace AppBundle\Controller\REST\v1;

use AppBundle\Entity\User;
use AppBundle\Util\GameUtil;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class StatusController extends Controller
{
    /**
     * @Route("/status", name="rest_v1_status")
     * @Method({"GET"})
     */
    public function statusAction()
    {
        $gameStatus = $this->get('game_status');
        $game = $gameStatus->getGameStatus();

        return new JsonResponse($game);
    }

    /**
     * @Route("/status/teams", name="rest_v1_status_teams")
     * @Method({"GET"})
     */
    public function teamStatusAction()
    {
        $gameStatus = $this->get('game_status');
        $teams = $gameStatus->getTeamStatus();

        return new JsonResponse($teams);
    }

    /**
     * @Route("/player/{id}", name="rest_v1_player", requirements={"id" = "\d+"})
     * @Method({"GET"})
     * @ParamConverter("user", class="AppBundle:User")
     */
    public function playerAction(User $user)
    {
        $gameStatus = $this->get('game_status');
        $player = $gameStatus->getPlayerInfo($user, false);

        return new JsonResponse($player);
    }

    /**
     * @Route("/players/{page}", name="rest_v1_players", requirements={"page" = "\d+"})
     * @Method({"GET"})
     */
    public function playersAction(Request $request, $page = 0)
    {
        $maxPerPage = $request->query->get('maxPerPage', 10);
        $sort = $request->query->get('sort', GameUtil::SORT_TEAM);

        if($maxPerPage > 30)
            return new JsonResponse(["status" => "error", "errors" => ["The maximum allowed per page is 30"]], 400);

        $gameStatus = $this->get('game_status');
        $list = $gameStatus->getPlayerList($page, $maxPerPage, $sort);

        return new JsonResponse($list);
    }

    /**
     * @Route("/players/search", name="rest_v1_players_search")
     * @Method({"GET"})
     */
    public function searchPlayersAction(Request $request)
    {
        $gameStatus = $this->get('game_status');

        $term = $request->query->get('term');
        if(strlen($term) < 3)
            return new JsonResponse(["status" => "error", "errors" => ["Search term must have at least three characters"]], 400);

        $list = $gameStatus->searchPlayerList($term);

        return new JsonResponse($list);
    }

    /**
     * @Route("/infections/{page}", name="rest_v1_infections", requirements={"page" = "\d+"})
     * @Method({"GET"})
     */
    public function infectionsAction(Request $request, $page = 0)
    {
        $maxPerPage = $request->query->get('maxPerPage', 10);

        if($maxPerPage > 30)
            return new JsonResponse(["status" => "error", "errors" => ["The maximum allowed per page is 30"]], 400);

        $gameStatus = $this->get('game_status');
        $list = $gameStatus->getInfectionList($page, $maxPerPage);
        foreach($list["infections"] as $k => $inf)
        {
            $list["infections"][$k]["time"] = $inf["time"]->getTimestamp();
        }

        return new JsonResponse($list);
    }
}