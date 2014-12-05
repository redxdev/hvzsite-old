<?php

namespace AppBundle\Controller\REST\v1;

use AppBundle\Service\ActionLogService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class GameController extends Controller
{
    /**
     * @Route("/register_infection", name="rest_v1_register_infection")
     * @Method({"POST"})
     */
    public function registerInfectionAction(Request $request)
    {
        $gameAuth = $this->get('game_authentication');

        $apikey = $request->query->get('apikey');
        $result = $gameAuth->processApiKey($apikey);

        if($result["status"] !== "ok")
        {
            return new JsonResponse($result, 400);
        }

        $user = $result["user"];

        $humanIdStr = $request->request->get('human');
        $zombieIdStr = $request->request->get('zombie');
        $latitude = $request->request->get('latitude');
        $longitude = $request->request->get('longitude');

        $gameManager = $this->get('game_manager');
        $result = $gameManager->processInfection($humanIdStr, $zombieIdStr, $latitude, $longitude);

        if($result["status"] !== "ok")
        {
            $entityManager = $this->getDoctrine()->getManager();
            $actLog = $this->get('action_log');

            $user->setApiFails($user->getApiFails() + 1);
            $actLog->record(
                ActionLogService::TYPE_API,
                $user->getEmail(),
                'API fail triggered - register infection endpoint',
                false
            );

            $entityManager->flush();

            $result["errors"][] = "Warning: Too many failed attempts will disable your API key";

            return new JsonResponse($result, 400);
        }

        return new JsonResponse($result);
    }

    /**
     * @Route("/antivirus", name="rest_v1_antivirus")
     * @Method({"POST"})
     */
    public function antivirusAction(Request $request)
    {
        $gameAuth = $this->get('game_authentication');

        $apikey = $request->query->get('apikey');
        $result = $gameAuth->processApiKey($apikey);

        if($result["status"] !== "ok")
        {
            return new JsonResponse($result, 400);
        }

        $user = $result["user"];

        $avIdStr = $request->request->get('antivirus');
        $zombieIdStr = $request->request->get('zombie');

        $gameManager = $this->get('game_manager');
        $result = $gameManager->processAntiVirus($avIdStr, $zombieIdStr);

        if($result["status"] !== "ok")
        {
            $entityManager = $this->getDoctrine()->getManager();
            $actLog = $this->get('action_log');

            $user->setApiFails($user->getApiFails() + 1);
            $actLog->record(
                ActionLogService::TYPE_API,
                $user->getEmail(),
                'API fail triggered - antivirus endpoint',
                false
            );

            $entityManager->flush();

            $result["errors"][] = "Warning: Too many failed attempts will disable your API key";

            return new JsonResponse($result, 400);
        }

        return new JsonResponse($result);
    }

    /**
     * @Route("/antivirus/valid_time", name="rest_v1_antivirus_valid_time")
     * @Method({"GET"})
     */
    public function validAntivirusTimeAction()
    {
        $gameManager = $this->get('game_manager');
        $valid_time = $gameManager->isValidAntiVirusTime();

        return new JsonResponse(["result" => $valid_time]);
    }
}