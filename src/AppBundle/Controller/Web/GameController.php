<?php

namespace AppBundle\Controller\Web;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class GameController extends Controller
{
    /**
     * @Route("/register_infection", name="web_register_infection")
     * @Method({"GET"})
     */
    public function registerInfectionAction()
    {
        $content = $this->renderView(
            ":Game:register_infection.html.twig",
            []
        );

        return new Response($content);
    }

    /**
     * @Route("/register_infection", name="web_register_infection_submit")
     * @Method({"POST"})
     */
    public function registerInfectionSubmitAction(Request $request)
    {
        $token = $request->get('_token');
        $humanIdStr = $request->get('human');
        $zombieIdStr = $request->get('zombie');
        $latitude = $request->get('latitude') or null;
        $longitude = $request->get('longitude') or null;

        if(!$this->isCsrfTokenValid("hvz_register_infection", $token))
        {
            $content = $this->renderView(
                ":Game:register_infection.html.twig",
                [
                    "status" => "error",
                    "errors" => ["Invalid CSRF token; try resubmitting the form."],
                    "human" => $humanIdStr,
                    "zombie" => $zombieIdStr
                ]
            );

            return new Response($content);
        }

        $gameManager = $this->get('game_manager');
        $result = $gameManager->processInfection($humanIdStr, $zombieIdStr, $latitude, $longitude);

        if($result["status"] == "ok")
        {
            $this->get('session')->getFlashBag()->add(
                'page.toast',
                $result["human_name"] . " has joined the horde, courtesy of " . $result["zombie_name"]
            );
        }

        $content = $this->renderView(
            ":Game:register_infection.html.twig",
            $result
        );

        return new Response($content);
    }
}