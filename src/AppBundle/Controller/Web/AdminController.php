<?php

namespace AppBundle\Controller\Web;

use AppBundle\Util\GameUtil;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AdminController extends Controller
{
    /**
     * @Route("/admin/players/{page}", name="web_admin_players", requirements={"page" = "\d+"})
     * @Security("is_granted('ROLE_MOD')")
     */
    public function playersAction($page = 0)
    {
        $gameStatus = $this->get('game_status');

        $list = $gameStatus->getPlayerList($page, 10, GameUtil::SORT_ALL, true, true);
        $list["page"] = $page;

        $content = $this->renderView(
            ":Admin:players.html.twig",
            $list
        );

        return new Response($content);
    }

    /**
     * @Route("/admin/players/search", name="web_admin_players_search")
     * @Security("is_granted('ROLE_MOD')")
     */
    public function playersSearchAction(Request $request)
    {
        $gameStatus = $this->get('game_status');

        $term = $request->query->get('term');
        $list = $gameStatus->searchPlayerList($term, false, true);
        $list["search_term"] = $term;

        $content = $this->renderView(
            ":Admin:players.html.twig",
            $list
        );

        return new Response($content);
    }
}