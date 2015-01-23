<?php

namespace AppBundle\Controller\REST\v1;

use AppBundle\Service\ActionLogService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class ProfileController extends Controller
{
    /**
     * @Route("/profile", name="rest_v1_profile")
     * @Method({"GET"})
     */
    public function profileAction(Request $request)
    {
        $gameAuth = $this->get('game_authentication');

        $apikey = $request->query->get('apikey');
        $result = $gameAuth->processApiKey($apikey);

        if($result["status"] !== "ok")
        {
            return new JsonResponse($result, 400);
        }

        $user = $result["user"];

        $profileManager = $this->get('profile_manager');

        $result = $profileManager->getProfileInfo($user);

        return new JsonResponse($result);
    }

    /**
     * @Route("/profile/clan", name="rest_v1_profile_clan_change")
     * @Method({"POST"})
     */
    public function changeClanAction(Request $request)
    {
        $gameAuth = $this->get('game_authentication');

        $apikey = $request->query->get('apikey');
        $result = $gameAuth->processApiKey($apikey);

        if($result["status"] !== "ok")
        {
            return new JsonResponse($result, 400);
        }

        $user = $result["user"];

        $clan = trim($request->request->get('clan'));
        if(strlen($clan) > 32)
        {
            return new JsonResponse(["status" => "error", "errors" => ["Clan name is too long"]], 400);
        }

        $entityManager = $this->getDoctrine()->getManager();
        $actLog = $this->get('action_log');

        $user->setClan($clan);
        $actLog->record(
            ActionLogService::TYPE_API . '+' . ActionLogService::TYPE_PROFILE,
            $user->getEmail(),
            'changed clan to ' . $clan,
            false
        );

        $entityManager->flush();

        return new JsonResponse(["status" => "ok"]);
    }
}