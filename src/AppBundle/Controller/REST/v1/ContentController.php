<?php

namespace AppBundle\Controller\REST\v1;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class ContentController extends Controller
{
    /**
     * @Route("/rules", name="rest_v1_rules")
     * @Method({"GET"})
     */
    public function rulesAction()
    {
        $contentManager = $this->get('content_manager');

        return new JsonResponse($contentManager->getRulesetList());
    }

    /**
     * @Route("/missions", name="rest_v1_missions")
     * @Method({"GET"})
     */
    public function missionsAction(Request $request)
    {
        $gameAuth = $this->get('game_authentication');

        $apikey = $request->query->get('apikey');
        $result = $gameAuth->processApiKey($apikey);

        if($result["status"] !== "ok")
        {
            return new JsonResponse($result, 400);
        }

        $user = $result["user"];

        $contentManager = $this->get('content_manager');

        return new JsonResponse($contentManager->getTeamMissionList($user->getTeam()));
    }
}