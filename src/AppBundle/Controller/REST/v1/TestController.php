<?php

namespace AppBundle\Controller\REST\v1;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class TestController extends Controller
{
    /**
     * @Route("/test/key", name="rest_v1_test_key")
     * @Method({"GET"})
     */
    public function testApiKey(Request $request)
    {
        $gameAuth = $this->get('game_authentication');

        $apikey = $request->query->get('apikey');
        $result = $gameAuth->processApiKey($apikey);

        if($result === null)
        {
            return new JsonResponse(["status" => "error", "errors" => ["Invalid API key"]], 400);
        }

        if(is_string($result))
        {
            return new JsonResponse(["status" => "error", "errors" => [$result]], 400);
        }

        return new JsonResponse(["status" => "ok", "user_id" => $result->getId()]);
    }
}